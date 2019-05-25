<?php
namespace Generation\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

if(!file_exists(__DIR__ . '/../../../config.php')) {
   
  return false;
}
require_once(__DIR__  . '/../../../config.php');

class ClearCache extends Command {
  private $confirmation;
  private $status=0;
  protected function configure() {

      $this->setName("clearcache")
           ->setDescription("Clear cache of the project");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    require_once('language/language.php');

    $this->confirmation = array(
     'y',
     'Y',
     'n',
     'N',
    );

    $helper = $this->getHelper('question');
    $q1 = new Question('<question>' . $language['question1'] . '</question>: ');
    $q2 = new Question('<question>' . $language['question2'] . '</question> : ');
    $q3 = new Question('<question>' . $language['confirm'] . '</question> :');

    while(!$this->status) {

      $username = $helper->ask($input, $output, $q1);
      $password = $helper->ask($input, $output, $q2);

      $conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD,DB_DATABASE);

      if (!$conn) {

        $output->writeln($language['connection_error']);
      }

      $query = "SELECT * from `" . DB_PREFIX . "user` where username = '" . $username . "' and (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . htmlspecialchars($password, ENT_QUOTES) . "'))))) OR password = '" . md5($password) . "') ";

      $result = mysqli_query($conn,$query);
      
      if (mysqli_num_rows($result) > 0) {

        $output->writeln($language['login_success']);
        $this->status = 1;
      } else {
        $output->writeln($language['login_error']);
        $this->status = 0;
      }
    }
    if($this->status) {
      $confirm = $helper->ask($input, $output, $q3);
      while (!in_array($confirm,$this->confirmation)) {
        $confirm = $helper->ask($input, $output, $q3);
      }
    }
    if($confirm == 'y' || $confirm == 'Y') {
      array_map('unlink', glob(DIR_SYSTEM . "storage/cache/*.*"));
      $output->writeln($language['cache_clear']);
    }else {
      $output->writeln($language['bye_bye']);
    }

  }
}
?>
