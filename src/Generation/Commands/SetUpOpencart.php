<?php
namespace Generation\Commands;
/**
 * to download the opencart zip and create the opencart setup
 */
 use Symfony\Component\Console\Command\Command;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;
 use Symfony\Component\Console\Input\InputOption;

class SetUpOpencart extends Command
{

      private $data = array();

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
        'destination',
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
            );
      }

      protected function execute(InputInterface $input, OutputInterface $output)
      {
            require_once('language/english/language.php');
            $this->data = $data;

            $this->options = $input;

            $this->output = $output;

            // to check all required options
            foreach($this->required_options as $option) {
              if (!$input->getOption($option)) {
                $this->error[$option] = $option;
                $output->writeln(ucwords(str_replace("_"," ",$option)) . $this->data['text_option_required']);
              }
            }

              if ($this->error) {
                exit;
              } else {
                $output->writeln([
                    '',
                    '********   ' . $this->data["text_welcome"] . ' ********',
                    '===========================================',
                    '',
                    '',
                ]);
                $destination = $input->getOption('destination');
                if (!$destination || !is_dir($destination)) {
                  exit($this->data['error_destination']);
                } else {
                  $this->destination = $destination;
                  $this->dir_path = getcwd();

                  // to check database details and if database not found then create
                  $this->checkDatabase();
                }
              }

               // To download provided opencart version
              if ($this->downloadOpencartZip()) {
                $output->writeln([
                    '',
                    '********   ' . $this->data["text_install_step"] . ' ********',
                    '===========================================',
                    '',
                    '',
                ]);
                $version = '/opencart-' . $input->getOption('oc_version');
                $command   = 'php ' . $this->destination . $version . '/upload/install/cli_install.php install' . ' --db_hostname ' . $input->getOption('db_hostname') . ' --db_username ' . $input->getOption('db_username') . ' --db_password ' . $input->getOption('db_password') . ' --db_database ' . $input->getOption('db_database') . ' --db_driver mysqli --db_port ' . $input->getOption('db_port') . ' --db_prefix ' . $input->getOption('db_prefix') . ' --username ' . $input->getOption('username') . ' --password ' . $input->getOption('password') . ' --email ' . $input->getOption('email') . ' --http_server ' . $input->getOption('http_server') . $version . '/upload/';
                echo shell_exec($command);

                unlink($this->dir_path . '/' . $input->getOption('oc_version') . '.zip');
              } else {
                exit($this->data['error_download']);
              }
          }

          public function downloadOpencartZip()
          {
              $status = false;
              $path      = $this->dir_path . '/' . $this->options->getOption('oc_version') . '.zip';
              $output = shell_exec($this->dir_path . '/app/install_oc.sh ' . $this->options->getOption('oc_version'));

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
          $connection =mysqli_connect($this->options->getOption('db_hostname'),$this->options->getOption('db_username'),$this->options->getOption('db_password'));

          if(!$connection) {
            exit($this->data['error_connection']);
          } else {
            $db = $this->options->getOption('db_database');
            if(!mysqli_select_db($connection,$db)) {
              $sql = "CREATE DATABASE IF NOT EXISTS " . $db;
              mysqli_query($connection,$sql);
            }
          }
        }
}
