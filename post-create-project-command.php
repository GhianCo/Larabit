<?php

echo <<<EOF

\033[32mProyecto creado con exito!\033[37m

Ejecuta los siguientes comandass:

$ cd [path-project]

\033[33mRecuerda!\033[37m
Configura tus accesos de la base de datos en: '.env'.

EOF;

unlink('.coveralls.yml');
unlink('post-create-project-command.php');
