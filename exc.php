<?php
function inverse($x) {
    if (!$x) {
        throw new Exception('Divisão por zero.');
    }
    return 1/$x;
}

try {
    echo inverse(5) . "<br>";
} catch (Exception $e) {
    echo 'Exceção capturada: ',  $e->getMessage(), "<br>";
} finally {
    echo "Primeiro finaly.<br>";
}

try {
    echo inverse(0) . "<br>";
} catch (Exception $e) {
    echo 'Exceção capturada: ',  $e->getMessage(), "<br>";
} finally {
    echo "Segundo finally.<br>";
}

// Execução continua
echo "Olá mundo<br>";
?>
