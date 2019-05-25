<?php

namespace Generation\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\Question;

class CreateExtension extends Command {

    private $dir_admin_controller1 = "controller/module/";
    private $dir_admin_controller2 = "controller/extension/module/";
    private $dir_admin_model1 = "model/module/";
    private $dir_admin_model2 = "model/extension/module/";
    private $dir_admin_view1 = "view/template/module/";
    private $dir_admin_view2 = "view/template/extension/module/";
    private $dir_catalog_view1 = "catalog/view/theme/default/template/module/";
    private $dir_catalog_view2 = "catalog/view/theme/default/template/extension/module/";
    private $dir_admin_lang1 = "language/english/module/";
    private $dir_admin_lang2 = "language/en-gb/module/";
    private $dir_admin_lang3 = "language/en-gb/extension/module/";
    private $ext_name_error;
    private $ext_file_error;
    private $error;
    private $language;
    private $confirmation;

    protected function configure() {
        $this->setName("generate:module")
             ->setDescription("It is to create new extension for opencart");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        require_once('Resources/language-en.php');
        $this->language = $language;
        $this->confirmation = array(
         'y',
         'Y',
         'n',
         'N',
        );

        $style = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('error', $style);
        $helper = $this->getHelper('question');
        $q1 = new Question('<question>' . $this->language['text_ask_version'] . '</question>: ');
        $q2 = new Question('<question>' . $this->language['text_ask_ext_name'] . '</question> : ');
        $q3 = new Question('<question>' . $this->language['text_ask_file_name'] . '</question> : ');
        $q4 = new Question('<question>' . $this->language['text_ask_model_file'] . '</question>: ');
        $q5 = new Question('<question>' . $this->language['text_ask_catalog_module'] . '</question>: ');
        $q6 = new Question('<question>' . $this->language['text_ask_confirm'] . '</question>');
        $details = array();

        $details['version'] = $helper->ask($input, $output, $q1);
        if($details['version'] != '') {
            while (!$this->checkVersion($details['version'])) {
                $output->writeln("<error>" . sprintf($this->language['error_version'], $details['version']) . "</error>");
                $details['version'] = $helper->ask($input, $output, $q1);
            }
        } else {
            // Not working in loop
            $output->writeln("\n<error>" . $this->language['error_version_blank'] . "</error>\n");
            $details['version'] = $helper->ask($input, $output, $q1);
        }
        $details['ext_name'] = $helper->ask($input, $output, $q2);

        while (!$this->checkExtName($details)) {
            $output->writeln("<error>" . $this->ext_name_error . "</error>");
            $details['ext_name'] = $helper->ask($input, $output, $q2);
        }

        $details['file_name'] = $helper->ask($input, $output, $q3);
        if($details['file_name'] == '') {
            $details['file_name'] = $details['ext_name'];
        }

        if (!$this->checkFileName($details)) {
            $output->writeln("<error>" . $this->ext_file_error . "</error>");
            $details['file_name'] = $helper->ask($input, $output, $q3);
        }

        $details['isModel'] = $helper->ask($input, $output, $q4);
        while (!$this->checkModelConfirmation($details)) {
            $output->writeln("<error>" . $this->language['error_no_answer'] . "</error>");
            $details['isModel'] = $helper->ask($input, $output, $q4);
        }

        $details['isCatalog'] = $helper->ask($input, $output, $q5);

        while (!$this->checkCatalogModConfirmation($details)) {
            $output->writeln("<error>" . $this->language['error_no_answer'] . "</error>");
            $details['isCatalog'] = $helper->ask($input, $output, $q5);
        }

        $output->writeln(sprintf($this->language['text_opencart_version'], $details['version']));
        $output->writeln(sprintf($this->language['text_extension_name'], $details['ext_name']));
        $output->writeln(sprintf($this->language['text_filename'], $details['file_name']));
        $output->writeln(sprintf($this->language['text_model_file'], $details['isModel']));
        $output->writeln(sprintf($this->language['text_at_catalog'], $details['isCatalog']));

        $details['confirmation'] = $helper->ask($input, $output, $q6);

        while (!$this->checkModConfirmation($details)) {
            $output->writeln("<error>" . $this->language['error_no_answer'] . "</error>");
            $details['confirmation'] = $helper->ask($input, $output, $q6);
        }

        if($details['confirmation'] == 'y' || $details['confirmation'] == 'Y') {
            $details['file_name'] = preg_replace('/[^a-zA-Z_\.]/', '_', $details['file_name']);
            $this->generateExt($details);
            $output->writeln('<info>' . $this->language['success_mod_created'] . '</info>');
        } else {
            $output->writeln('<info>' . $this->language['success_abort_process'] . '</info>');
        }
    }

    private function checkVersion($version) {
        $versions = array(
         '2.0.0.0',
         '2.0.1.0', 
         '2.0.1.1',
         '2.0.2.0',
         '2.0.3.1',
         '2.1.0.1',
         '2.1.0.2',
         '2.2.0.0', 
         '2.3.0.0', 
         '2.3.0.1', 
         '2.3.0.2',
         '3.0.2.0',
         '3.0.3.1',
         '3.0.3.2',
        );
        if(in_array($version, $versions)) {
            // require_once('index.php');
            // if(VERSION != $version) {
            //     $this->error  = "your current system is not running the same version";
            //     return false;
            // } else {
            //     $this->error = "version is not allowed";
            // }
            return true;
        } else {
            return false;
        }
    }

    public function checkModelConfirmation($details) {
        if($details['isModel'] == '' || !in_array($details['isModel'], $this->confirmation)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkCatalogModConfirmation($details) {
        if($details['isCatalog'] == '' || !in_array($details['isCatalog'], $this->confirmation)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkModConfirmation($details) {
        if($details['confirmation'] == '' || !in_array($details['confirmation'], $this->confirmation)) {
            return false;
        } else {
            return true;
        }
    }

    private function checkExtName($details) {
        if($details['ext_name'] == '') {
            $this->ext_name_error = $this->language['error_ext_name'];
            return false;
        } else {
            $cur_path = getcwd() . '/';
            if(version_compare($details['version'], '2.2.0.0') >= 0) {
                if (version_compare($details['version'], '2.3.0.0') >= 0) {
                    $path = $cur_path . 'admin/' . $this->dir_admin_lang3;
                } else {
                    $path = $cur_path . 'admin/' . $this->dir_admin_lang2;
                }
            } else {
                $path = $cur_path . 'admin/' . $this->dir_admin_lang1;
            }
            // if(!file_exists($path)) {
            //     $this->ext_name_error = $this->language['error_dir_structure'];
            //     return false;
            // }
            if(file_exists($path)) {
              $files = scandir($path);
              unset($files[0]);unset($files[1]);
              foreach ($files as $key => $file) {
                if(file_exists($path . $file)) {
                  include_once($path . $file);
                  if(isset($_) && $_ && isset($_['heading_title']) && $_['heading_title'] == ucfirst($details['ext_name'])) {
                    $this->ext_name_error = $this->language['error_extension_exist'];
                    return false;
                  }
                }
              }
            }
            return true;
        }
    }

    private function checkFileName($details) {
        if($details['file_name'] == '') {
            $this->ext_file_error = $this->language['error_file_name'];
            return false;
        } else {
            $cur_path = getcwd() . '/';
            if (version_compare($details['version'], '2.3.0.0', '>=')) {
                $path = $cur_path . 'admin/' . $this->dir_admin_controller2;
            } else {
                $path = $cur_path . 'admin/' . $this->dir_admin_controller1;
            }

            if(file_exists($path . $details['file_name'] . '.php')) {
                $this->ext_file_error = $this->language['error_file_exist'];
                return false;
            }
            return true;
        }
    }

    private function generateExt($details) {
        $this->constructController($details);
        $this->constructView($details);
        $this->constructLanguage($details);
        if($details['isModel'] == 'y' || $details['isModel'] == 'Y' || $details['isModel'] != '') {
            $this->constructModel($details);
        }
        if($details['isCatalog'] == 'y' || $details['isCatalog'] == 'Y') {
            $this->constructControllerCatalog($details);
            $this->constructViewCatalog($details);
            $this->constructLanguageCatalog($details);
            if($details['isModel'] == 'y' || $details['isModel'] == 'Y' || $details['isModel'] != '') {
                $this->constructModelCatalog($details);
            }
        }
    }

    private function constructController($details) {
        $cur_path = getcwd() . '/';
        if (version_compare($details['version'], '2.3.0.0') >= 0) {
            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_controller2)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_controller2, 0777, true);
            }
            $controller_write = fopen($cur_path . 'admin/' . $this->dir_admin_controller2 . $details['file_name'] . ".php", "w");
            $controller = $this->genExtModController($details,$cur_path);
        } else {
            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_controller1)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_controller1, 0777, true);
            }
            $controller_write = fopen($cur_path . 'admin/' . $this->dir_admin_controller1 . $details['file_name'] . ".php", "w");
            $controller = $this->genModController($details,$cur_path);
        }

        fwrite($controller_write, $controller);
        fclose($controller_write);
    }

    private function constructControllerCatalog($details) {
        $cur_path = getcwd() . '/';
        if (version_compare($details['version'], '2.3.0.0') >= 0) {
            if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_controller2)) {
              mkdir($cur_path . 'catalog/' . $this->dir_admin_controller2, 0777, true);
            }
            $controller_write = fopen($cur_path . 'catalog/' . $this->dir_admin_controller2 . $details['file_name'] . ".php", "w");
            $controller = $this->genExtModControllerCatalog($details,$cur_path);
        } else {
            if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_controller1)) {
              mkdir($cur_path . 'catalog/' . $this->dir_admin_controller1, 0777, true);
            }
            $controller_write = fopen($cur_path . 'catalog/' . $this->dir_admin_controller1 . $details['file_name'] . ".php", "w");
            $controller = $this->genModControllerCatalog($details,$cur_path);
        }

        fwrite($controller_write, $controller);
        fclose($controller_write);
    }

    private function constructView($details) {
        $cur_path = getcwd() . '/';
        $view = $this->genView($details,$cur_path);
        if (version_compare($details['version'], '3.0.0.0') >= 0) {

            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_view2)) {
                mkdir($cur_path . 'admin/' . $this->dir_admin_view2, 0777, true);
              }
              $view_write = fopen($cur_path . 'admin/' . $this->dir_admin_view2 . $details['file_name'] . ".twig", "w");
            
        } elseif (version_compare($details['version'], '2.3.0.0') >= 0) {

            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_view2)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_view2, 0777, true);
            }

            $view_write = fopen($cur_path . 'admin/' . $this->dir_admin_view2 . $details['file_name'] . ".tpl", "w");
        } else {

            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_view1)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_view1, 0777, true);
            }

            $view_write = fopen($cur_path . 'admin/' . $this->dir_admin_view1 . $details['file_name'] . ".tpl", "w");
        }

        fwrite($view_write, $view);
        fclose($view_write);
    }

    private function constructViewCatalog($details) {
        $cur_path = getcwd() . '/';
        $view = $this->genViewCatalog($details,$cur_path);
        if (version_compare($details['version'], '3.0.0.0') >= 0) {
            
            if (!is_dir($cur_path . $this->dir_catalog_view2)) {
                mkdir($cur_path . $this->dir_catalog_view2, 0777, true);
            }            
            $view_write = fopen($cur_path . $this->dir_catalog_view2 . $details['file_name'] . ".twig", "w");
            
        } elseif (version_compare($details['version'], '2.3.0.0') >= 0) {
            if (!is_dir($cur_path . $this->dir_catalog_view2)) {
              mkdir($cur_path . $this->dir_catalog_view2, 0777, true);
            }
            $view_write = fopen($cur_path . $this->dir_catalog_view2 . $details['file_name'] . ".tpl", "w");
        } else {
            if (!is_dir($cur_path . $this->dir_catalog_view1)) {
              mkdir($cur_path . $this->dir_catalog_view1, 0777, true);
            }
            $view_write = fopen($cur_path . $this->dir_catalog_view1 . $details['file_name'] . ".tpl", "w");
        }
    }

    private function constructLanguage($details) {
        $cur_path = getcwd() . '/';
        $language = $this->genLang($details,$cur_path);
        if(version_compare($details['version'], '2.2.0.0') >= 0) {
            if (version_compare($details['version'], '2.3.0.0') >= 0) {
                if (!is_dir($cur_path . 'admin/' . $this->dir_admin_lang3)) {
                  mkdir($cur_path . 'admin/' . $this->dir_admin_lang3, 0777, true);
                }
                $language_write = fopen($cur_path . 'admin/' . $this->dir_admin_lang3 . $details['file_name'] . ".php", "w");
            } else {
                if (!is_dir($cur_path . 'admin/' . $this->dir_admin_lang2)) {
                  mkdir($cur_path . 'admin/' . $this->dir_admin_lang2, 0777, true);
                }
                $language_write = fopen($cur_path . 'admin/' . $this->dir_admin_lang2 . $details['file_name'] . ".php", "w");
            }
        } else {
            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_lang1)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_lang1, 0777, true);
            }
            $language_write = fopen($cur_path . 'admin/' . $this->dir_admin_lang1 . $details['file_name'] . ".php", "w");
        }
        fwrite($language_write, $language);
        fclose($language_write);
    }

    private function constructLanguageCatalog($details) {
        $cur_path = getcwd() . '/';
        // $language = $this->genLang($details,$cur_path);
        if(version_compare($details['version'], '2.2.0.0') >= 0) {
            if (version_compare($details['version'], '2.3.0.0') >= 0) {
                if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_lang3)) {
                  mkdir($cur_path . 'catalog/' . $this->dir_admin_lang3, 0777, true);
                }
                $language_write = fopen($cur_path . 'catalog/' . $this->dir_admin_lang3 . $details['file_name'] . ".php", "w");
            } else {
                if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_lang2)) {
                  mkdir($cur_path . 'catalog/' . $this->dir_admin_lang2, 0777, true);
                }
                $language_write = fopen($cur_path . 'catalog/' . $this->dir_admin_lang2 . $details['file_name'] . ".php", "w");
            }
        } else {
            if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_lang1)) {
              mkdir($cur_path . 'catalog/' . $this->dir_admin_lang1, 0777, true);
            }
            $language_write = fopen($cur_path . 'catalog/' . $this->dir_admin_lang1 . $details['file_name'] . ".php", "w");
        }
        // fwrite($languageWrite, $language);
        // fclose($languageWrite);
    }

    private function constructModel($details) {
        $cur_path = getcwd() . '/';
        if (version_compare($details['version'], '2.3.0.0') >= 0) {
            if(!file_exists($cur_path . "admin/model/extension/module")) {
                mkdir($cur_path . "admin/model/extension/module");
            }
            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_model2)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_model2, 0777, true);
            }
            $model_write = fopen($cur_path . 'admin/' . $this->dir_admin_model2 . $details['file_name'] . ".php", "w");
            $model = $this->genExtModel($details,$cur_path);
        } else {
            if(!file_exists($cur_path .  "admin/model/module")) {
                mkdir($cur_path . "admin/model/module", 0777, true);
            }
            if (!is_dir($cur_path . 'admin/' . $this->dir_admin_model1)) {
              mkdir($cur_path . 'admin/' . $this->dir_admin_model1, 0777, true);
            }
            $model_write = fopen($cur_path . 'admin/' . $this->dir_admin_model1 . $details['file_name'] . ".php", "w");
            $model = $this->genModel($details,$cur_path);
        }
        fwrite($model_write, $model);
        fclose($model_write);
    }

    private function constructModelCatalog($details) {

        $cur_path = getcwd() . '/';

        if (version_compare($details['version'], '2.3.0.0') >= 0) {

            if(!file_exists($cur_path . "catalog/model/extension/module")) {
                mkdir($cur_path . "catalog/model/extension/module", 0777, true);
            }

            if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_model2)) {
              mkdir($cur_path . 'catalog/' . $this->dir_admin_model2, 0777, true);
            }

            $model_write = fopen($cur_path . 'catalog/' . $this->dir_admin_model2 . $details['file_name'] . ".php", "w");
            $model = $this->genExtModel($details,$cur_path);
        } else {

            if(!file_exists($cur_path . "catalog/model/module")) {
                mkdir($cur_path . "catalog/model/module", 0777, true);
            }

            if (!is_dir($cur_path . 'catalog/' . $this->dir_admin_model1)) {
              mkdir($cur_path . 'catalog/' . $this->dir_admin_model1, 0777, true);
            }

            $model_write = fopen($cur_path . 'catalog/' . $this->dir_admin_model1 . $details['file_name'] . ".php", "w");
            $model = $this->genModel($details,$cur_path);
        }
        fwrite($model_write, $model);
        fclose($model_write);
    }

    private function genExtModController($details,$cur_path) {

        $file_content = '';

        if (version_compare($details['version'], '3.0.0.0') >= 0) {

            $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/3.x.x.x/extModController.txt', 'w');
        } else {

            $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/extModController.txt', 'w');
        }        

        $file = str_replace("consolefilename", $details['file_name'], $file);               
        $file_content = str_replace("-" . $details['file_name'], ucfirst($this->clearString($details['file_name'])), $file);

        return $file_content;
    }

    private function genExtModControllerCatalog($details,$cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/extModControllerCatalog.txt', 'w');
        $file = str_replace("consolefilename", $details['file_name'], $file);
        $file_content = str_replace("-" . $details['file_name'], ucfirst($this->clearString($details['file_name'])), $file);

        return $file_content;
    }

    private function genModController($details, $cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/extModController.txt', 'w');
        $file = str_replace("consolefilename", $details['file_name'], $file);
        $file_content = str_replace("-" . $details['file_name'], ucfirst($this->clearString($details['file_name'])), $file);
        return $file_content;
    }

    private function genModControllerCatalog($details, $cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/modControllerCatalog.txt', 'w');
        $file = str_replace("consolefilename", $details['file_name'], $file);
        $file_content = str_replace("-" . $details['file_name'], ucfirst($this->clearString($details['file_name'])), $file);
        return $file_content;
    }

    private function genView($details, $cur_path) {

        $file_content = '';
        if (version_compare($details['version'], '3.0.0.0') >= 0) {

            $file_content = str_replace("consolefilename", $details['file_name'], file_get_contents($cur_path . 'src/Generation/Commands/Resources/3.x.x.x/view.txt', 'w'));
        } else {

            $file_content = str_replace("consolefilename", $details['file_name'], file_get_contents($cur_path . 'src/Generation/Commands/Resources/view.txt', 'w'));   
        }        
        return $file_content;
    }

    private function genViewCatalog($details, $cur_path) {

        $file_content = '';
        if (version_compare($details['version'], '3.0.0.0') >= 0) {

            $file_content = file_get_contents($cur_path . 'src/Generation/Commands/Resources/3.x.x.x/viewCatalog.txt', 'w');
        } else {
            $file_content = file_get_contents($cur_path . 'src/Generation/Commands/Resources/viewCatalog.txt', 'w');
        }
        return $file_content;
    }

    private function genLang($details, $cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/lang.txt', 'w');
        $file_content = str_replace("consolefilename", ucfirst($this->clearString($details['ext_name'])), $file);
        return $file_content;
    }

    private function genModel($details, $cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/model.txt', 'w');
        $file_content = str_replace("-consolefilename", ucfirst($this->clearString($details['file_name'])), $file);
        return $file_content;
    }

    private function genExtModel($details, $cur_path) {

        $file_content = '';
        $file = file_get_contents($cur_path . 'src/Generation/Commands/Resources/extModel.txt', 'w');
        $file_content = str_replace("-consolefilename", ucfirst($this->clearString($details['file_name'])), $file);
        return $file_content;
    }

    private function clearString($string) {
        
       $string = str_replace(' ', ' ', $string);

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
