# Logging



    require_once "Log/LogDebug.php";
    $log= new \LogDebug();
    $log>log_ELK(70, "Включил режим mode", $_REQUEST);