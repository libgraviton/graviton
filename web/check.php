<?php

if (!isset($_SERVER['HTTP_HOST'])) {
    exit('This script cannot be run from the CLI. Run it from a browser.');
}

if (!in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
))) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: application/json');
    exit('{"fail":"This script is only accessible from localhost."}');
}
require_once dirname(__FILE__).'/../app/SymfonyRequirements.php';

$symfonyRequirements = new SymfonyRequirements();

$majorProblems = $symfonyRequirements->getFailedRequirements();
$minorProblems = $symfonyRequirements->getFailedRecommendations();

header('Content-Type: application/json');

?>
{
<?php if (count($majorProblems)): ?>
  "major": [
    <?php
      $str = '';
      foreach ($minorProblems as $problem) {
          $str .= '"'.strtr($problem->getHelpText(), '"', "'").'",';
      }
      echo substr($str, 0, -1);
    ?>
  ],
<?php endif; ?>
<?php if (count($minorProblems)): ?>
  "minor": [
    <?php
      $str = '';
      foreach ($minorProblems as $problem) {
          $str .= '"'.strtr($problem->getHelpText(), '"', "'").'",';
      }
      echo substr($str, 0, -1);
    ?>
  ],
<?php endif; ?>
<?php if ($symfonyRequirements->hasPhpIniConfigIssue()): ?>
  <?php if ($symfonyRequirements->getPhpIniConfigPath()): ?>
    "iniPath": "<?php echo $symfonyRequirements->getPhpIniConfigPath() ?>",
  <?php endif; ?>
<?php endif; ?>
<?php if (!count($majorProblems) && !count($minorProblems)): ?>
  "ok": true
<?php else: ?>
  "ok": false
<?php endif; ?>
}
