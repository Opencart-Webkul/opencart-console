<?php
namespace Generation\Commands;

if(!file_exists(__DIR__ . '/../../../config.php')) {

  return false;
}
require_once(__DIR__  . '/../../../config.php');

use Symfony\Component\Console\Command\Command;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;
 use Symfony\Component\Console\Input\InputArgument;
 use Symfony\Component\Console\Input\InputOption;
 use Symfony\Component\Console\Question\Question;

 class WkOcSql extends Command {

 	protected function configure() {

 		$this->setName('app:oc-sql')
      ->addArgument('commond_name', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Please enter product name.');
 	}

 	protected function execute(InputInterface $input, OutputInterface $output) {

     if(isset($input->getArgument('commond_name')[0]))
     {
        if($input->getArgument('commond_name')[0]=='oc_tables') {
           $this->getTables($output);
        } elseif ($input->getArgument('commond_name')[0]=='export') {
             if (isset($input->getArgument('commond_name')[1]) && $input->getArgument('commond_name')[1]!='') {
                $this->exportTable($output,$input->getArgument('commond_name')[1]);
              } else {
                $output->writeln('Invalid parameter.');
                die;
              }
           } elseif($input->getArgument('commond_name')[0]=='import') {
             if (isset($input->getArgument('commond_name')[1]) && $input->getArgument('commond_name')[1]!='') {
                $this->importTable($output,$input->getArgument('commond_name')[1]);
                //  $output->writeln('Invalid parameter.');
              } else {
                $output->writeln('Invalid parameter.');
                die;
              }


           } else {
             $output->writeln('Please select valid action name.');
             die;
           }
        }
     }

  private function connObject() {
    $conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD,DB_DATABASE);
      return $conn;
  }

  private function getTables($output) {
     $db = $this->connObject();

     $output->writeln(DIR_SYSTEM . 'library/db.php');
     $data = "SELECT table_name FROM information_schema.tables WHERE table_schema='" . DB_DATABASE . "'";

    $result = mysqli_query($db,$data);
    $i=1;
    while($row = $result->fetch_assoc()) {
      $output->writeln($i . ' Table name : ' . $row['table_name']);
      $i++;
    }
  }
private function exportTable($output, $table_name) {
  if (!is_dir('sql_export')) {
              mkdir('sql_export', 0777, true);
  }

  if ($table_name== 'all') {
    $command = 'mysqldump -u ' . DB_USERNAME . ' -p ' . DB_DATABASE . ' > ./sql_export/' . DB_DATABASE . '.sql';
      exec($command);
      $output->writeln(' Database successfully export.');
  } else {
    $command = 'mysqldump -u ' . DB_USERNAME . ' -p ' . DB_DATABASE . ' ' . $table_name . '> ./sql_export/' . $table_name . '.sql';
      exec($command);
      $output->writeln(' Table name successfully export : ' . $table_name);
  }
}
  private function importTable($output,$file_name) {
    if (!is_dir('sql_import')) {
                mkdir('sql_import', 0777, true);
    }
   $command = 'mysql -u ' . DB_USERNAME . ' -p ' . DB_DATABASE . ' < ./sql_import/' . $file_name . '.sql';
   $output->writeln($command);
    exec($command);
      $output->writeln(' Table import successfully: ' . $file_name);
  }

}
// console end

?>
