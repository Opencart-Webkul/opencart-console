<?php
namespace Generation\Commands;
/**
 * to download the opencart zip and create the opencart setup
 */
 use Symfony\Component\Console\Command\Command;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;
 use Symfony\Component\Console\Input\InputOption;
 use Symfony\Component\Console\Question\Question;

class SetUpOpencart extends Command
{

      private $language = array();

      private $dir_path = '';

      private $options = array();

      private $error = array();

      private $destination = array();

      private $output = array();

      private $required_options = array(
        'db_hostname',
        'db_username',
        'db_password',
        'db_database',
        'db_prefix',
        'db_port',
        'username',
        'password',
        'email',
        'http_server',
        'oc_version',
        'destination'
      );

      private $opencart_versions = array(
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
        '3.0.0.0',
        '3.0.1.1',
        '3.0.1.2',
        '3.0.2.0'
      );

      protected function configure()
      {
            $this
            // the name of the command (the part after "bin/console")
            ->setName('setup:install-opencart')

            // the short description shown while running "php bin/console list"
            ->setDescription('Download Opencart and Setup Opencart with Database.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to download and install any version of the opencart...')

            // the "--db_hostname=localhost" option
            ->addOption(
               'db_hostname',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database host'
            )

            // the "--db_username=webkul" option
            ->addOption(
               'db_username',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database username'
            )

            // the "--db_password=webkul" option
            ->addOption(
               'db_password',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database password'
            )

            // the "--db_database=console" option
            ->addOption(
               'db_database',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database name'
            )

            // the "--db_prefix=_oc" option
            ->addOption(
               'db_prefix',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database tables prefix'
            )

            // the "--db_port=80" option
            ->addOption(
               'db_port',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as database port'
            )

            // the "--username=admin" option
            ->addOption(
               'username',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as admin panel username'
            )

            // the "--password=admin" option
            ->addOption(
               'password',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as admin panel password'
            )

            // the "--email=johndoe@opencart.com" option
            ->addOption(
               'email',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as admin user email'
            )

            // the "--http_server=127.0.0.1" option
            ->addOption(
               'http_server',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will be used as server path'
            )

            // the "--oc_version=2.3.0.2" option
            ->addOption(
               'oc_version',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will install provided opencart version'
            )

            // the "--destination=/home/users/user_name/www/html/opencart_install" option
            ->addOption(
               'destination',
               null,
               InputOption::VALUE_REQUIRED,
               'If set, will install provided opencart version into destination path'
            )

            // the "--manual=1" option
            ->addOption(
               'manual',
               null,
               InputOption::VALUE_OPTIONAL,
               'If set, will ask for all required values to enter manually'
            );
      }

      protected function execute(InputInterface $input, OutputInterface $output)
      {
            require_once('language/english/language.php');
            $this->language = $data;

            $this->output = $output;

            $values_collection = array();
            if ($input->getOption('manual')) {
              $helper = $this->getHelper('question');
              foreach($this->required_options as $option) {
                $question = new Question('<question>' . $this->language[$option] . '</question>: ');
                $values_collection[$option] = $helper->ask($input, $output, $question);

                //Restrict for blank value
                while(!$values_collection[$option]) {
                  $output->writeln("\n<error>" . $this->language['error_wrong_answer'] . "</error>\n");
                  $values_collection[$option] = $helper->ask($input, $output, $question);
                }

                //validate for versions
                if ($option == 'oc_version') {
                  while(!in_array($values_collection[$option],$this->opencart_versions)) {
                    $output->writeln("\n<error>" . $this->language['error_version'] . "</error>\n");
                    foreach($this->opencart_versions as $version) {
                      $output->writeln($version . "\n");
                    }
                    $values_collection[$option] = $helper->ask($input, $output, $question);
                  }
                }
                
              }
              $this->options = $values_collection;

              //function call to validate directory
              while (!is_dir($this->options['destination'])) {
                $output->writeln("\n<error>" . $this->language['error_wrong_answer'] . "</error>\n");
                $this->options['destination'] = $helper->ask($input, $output, new Question('<question>'.$this->language['destination'] . '</question>: '));
              }
            } else {
              // to check all required options
              foreach($this->required_options as $option) {
                if (!$input->getOption($option)) {
                  $this->error[$option] = $option;
                  $output->writeln(ucwords(str_replace("_"," ",$option)) . $this->language['text_option_required']);
                } else {
                  $values_collection[$option] = $input->getOption($option);
                }
              }
              $this->options = $values_collection;
            }
              if ($this->error) {
                exit;
              } else {
                $output->writeln([
                    '',
                    '********   ' . $this->language["text_welcome"] . ' ********',
                    '===========================================',
                    '',
                    '',
                ]);
                $destination = $this->options['destination'];
                if (!$destination || !is_dir($destination)) {
                  exit($this->language['error_destination']);
                } else {
                  $this->destination = $destination;
                  $this->dir_path = getcwd();

                  // to check database details and if database not found then create
                  $this->checkDatabase();
                }
              }

               // To download provided opencart version
              if ($this->downloadOpencartZip()) {

                if (version_compare($this->options['oc_version'],'2.3.0.0','>=')) {
                  $file = $this->options['destination'] . 'opencart-'.$this->options['oc_version'] . '/upload/system/startup.php';
                  if (is_file($file)) {
                    $contents = file_get_contents($file);
                    $find = html_entity_decode('if ((isset($_SERVER[&#x27;HTTPS&#x27;]) && (($_SERVER[&#x27;HTTPS&#x27;] == &#x27;on&#x27;) || ($_SERVER[&#x27;HTTPS&#x27;] == &#x27;1&#x27;))) || $_SERVER[&#x27;SERVER_PORT&#x27;] == 443) {',ENT_QUOTES,"UTF-8");
                    $find1 = html_entity_decode('if (is_file(DIR_STORAGE . &#x27;vendor/autoload.php&#x27;)) {',ENT_QUOTES,"UTF-8");
                    $replace = html_entity_decode('if ((isset($_SERVER[&#x27;HTTPS&#x27;]) && (($_SERVER[&#x27;HTTPS&#x27;] == &#x27;on&#x27;) || ($_SERVER[&#x27;HTTPS&#x27;] == &#x27;1&#x27;))) || (isset($_SERVER[&#x27;SERVER_PORT&#x27;]) && $_SERVER[&#x27;SERVER_PORT&#x27;] == 443)) {',ENT_QUOTES,"UTF-8");
                    $replace1 = html_entity_decode('if (defined(&#x27;DIR_STORAGE&#x27;) && is_file(DIR_STORAGE . &#x27;vendor/autoload.php&#x27;)) {',ENT_QUOTES,"UTF-8");
                    $contents = str_replace($find,$replace,$contents);
                    $contents = str_replace($find1,$replace1,$contents);
                    file_put_contents($file,$contents);
                  }
                }
                $output->writeln([
                    '',
                    '********   ' . $this->language["text_install_step"] . ' ********',
                    '===========================================',
                    '',
                    '',
                ]);
                $version = '/opencart-' . $this->options['oc_version'];
                $command   = 'php ' . $this->destination . $version . '/upload/install/cli_install.php install' . ' --db_hostname ' . $this->options['db_hostname'] . ' --db_username ' . $this->options['db_username'] . ' --db_password ' . $this->options['db_password'] . ' --db_database ' . $this->options['db_database'] . ' --db_driver mysqli --db_port ' . $this->options['db_port'] . ' --db_prefix ' . $this->options['db_prefix'] . ' --username ' . $this->options['username'] . ' --password ' . $this->options['password'] . ' --email ' . $this->options['email'] . ' --http_server ' . $this->options['http_server'] . $version . '/upload/';
                echo shell_exec($command);

                unlink($this->dir_path . '/' . $this->options['oc_version'] . '.zip');
              } else {
                exit($this->language['error_download']);
              }
          }

          public function downloadOpencartZip()
          {
              $status = false;
              $path      = $this->dir_path . '/' . $this->options['oc_version'] . '.zip';
              $output = shell_exec($this->dir_path . '/app/install_oc.sh ' . $this->options['oc_version']);

              if ((int)$output) {
                  if (file_exists($path)) {
                          $version_zip= new \ZipArchive;
                          if ($version_zip->open($path) === TRUE) {
                              $version_zip->extractTo($this->destination);
                              $version_zip->close();
                              $status = true;
                          }
                  }
              }
              return $status;
        }

        public function checkDatabase()
        {
          $connection = @mysqli_connect($this->options['db_hostname'],$this->options['db_username'],$this->options['db_password']);

          if(!$connection) {
            exit($this->language['error_connection']."\n\n");
          } else {
            $db = $this->options['db_database'];
            if(!mysqli_select_db($connection,$db)) {
              $sql = "CREATE DATABASE IF NOT EXISTS " . $db;
              mysqli_query($connection,$sql);
            }
          }
        }
}
