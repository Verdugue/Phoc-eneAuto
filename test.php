<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded php.ini: " . php_ini_loaded_file() . "\n";
echo "Extensions loaded:\n";
print_r(get_loaded_extensions());
echo "\nPDO drivers available:\n";
print_r(PDO::getAvailableDrivers()); 