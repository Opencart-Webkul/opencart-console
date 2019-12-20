<?php

namespace Cardinity\Method;

use Cardinity\Exception;
use Cardinity\Method\Payment\AuthorizationInformation;
use Cardinity\Method\Payment\PaymentInstrumentCard;
use Cardinity\Method\Payment\PaymentInstrumentRecurring;

abstract class ResultObject implements ResultObjectInterface
{
    /**
     * Wrap single result error into array of errors
     * @return array
     */
    public function getErrors()
    {
        return [
            [
                'field' => 'status',
                'message' => $this->getError()
            ]
        ];
    }

    /**
     * Return single error
     */
    public function getError()
    {
        return '';
    }

    /**
     * Serializes result object to json object
     * @param boolean $toJson encode result to json
     * @return string
     */
    public function serialize($toJson = true)
    {
        $data = [];

        $getters = $this->classGetters(get_class($this));
        foreach ($getters as $method) {
            $property = $this->propertyName($method);
            $value = $this->$method();

            if (is_float($value)) {
                $value = sprintf("%01.2f", $value);
            } elseif (is_object($value)) {
                $value = $value->serialize(false);
            }

            if ($value !== null) {
                $data[$property] = $value;
            }
        }

        if ($toJson === true) {
            return json_encode($data);
        }

        return $data;
    }

    /**
     * Loads result object values from json object
     * @param string $string json
     * @return void
     */
    public function unserialize($string)
    {
        $data = json_decode($string);
        foreach ($data as $property => $value) {
            $method = $this->setterName($property);

            if (is_numeric($value) && strstr($value, '.')) {
                $value = floatval($value);
            } elseif (is_object($value)) {
                if ($property == 'authorization_information') {
                    $object = new AuthorizationInformation();
                    $object->unserialize(json_encode($value));
                    $value = $object;
                } elseif ($property == 'payment_instrument') {
                    if (!isset($data->payment_method)) {
                        throw new Exception\Runtime('Property "payment_method" is missing');
                    }

                    if ($data->payment_method == Payment\Create::CARD) {
                        $object = new PaymentInstrumentCard();
                    } elseif ($data->payment_method == Payment\Create::RECURRING) {
                        $object = new PaymentInstrumentRecurring();
                    }
                    $object->unserialize(json_encode($value));
                    $value = $object;
                }
            }

            $this->$method($value);
        }
    }

    /**
     * @param string $class
     */
    private function classGetters($class)
    {
        $methods = get_class_methods($class);
        return array_filter($methods, function ($value) use ($methods) {
            if ($value == 'getErrors') {
                return false;
            }

            // no setter means it's inherited property, should be ignored
            $setter = $this->setterName($this->propertyName($value));
            if (!in_array($setter, $methods)) {
                return false;
            }

            return substr($value, 0, 3) == 'get';
        });
    }

    private function propertyName($method)
    {
        $method = lcfirst(substr($method, 3));
        $method = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $method));
        return $method;
    }

    private function setterName($property)
    {
        $parts = explode('_', $property);
        $parts = array_map('ucfirst', $parts);
        $property = implode('', $parts);

        return 'set' . ucfirst($property);
    }
}
