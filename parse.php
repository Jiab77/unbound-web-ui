#!/usr/bin/env php

<?php
$file_to_read = '/var/log/syslog';
$file = new SplFileObject($file_to_read);
$file->seek(PHP_INT_MAX); // cheap trick to seek to EoF
$total_lines = $file->key(); // last line number
$lines_to_read = 1;

do {
    // output the last twenty lines
    $reader = new LimitIterator($file, $total_lines - $lines_to_read);
    $stored_lines = [];
    foreach ($reader as $line) {
        // display lines
        // echo $line . PHP_EOL; // includes newlines

        // store lines
        $stored_lines[] = $line;
    }
    $unique_lines = array_unique($stored_lines);
    foreach ($unique_lines as $line) {
        if (stripos(php_sapi_name(), 'cli') === false) {
            echo '<pre>' . PHP_EOL;
        }

        // parse lines
        $line = trim($line);
        if (strpos($line, 'NXDOMAIN') !== false) {
            $parsed_array = explode(' ', $line);
            list($service_pid, $service_thread) = explode(':', str_replace(['[',']'], '', $parsed_array[5]));

            $parsed_object = new stdClass();
            $parsed_object->date = $parsed_array[0] . ' ' . $parsed_array[1] . ' ' . $parsed_array[2];
            $parsed_object->host = $parsed_array[3];
            $parsed_object->service = $parsed_array[4];
            $parsed_object->service_pid = $service_pid;
            $parsed_object->service_thread = $service_thread;
            $parsed_object->log_type = $parsed_array[6];
            $parsed_object->client = $parsed_array[7];
            $parsed_object->request = $parsed_array[8];
            $parsed_object->request_type = $parsed_array[9];
            $parsed_object->request_class = $parsed_array[10];
            $parsed_object->responce_type = $parsed_array[11];
            $parsed_object->responce_time = $parsed_array[12];
            $parsed_object->responce_code = $parsed_array[13];
            $parsed_object->responce_size = $parsed_array[14];

            $parsed_json = json_encode($parsed_object);
            // print_r($parsed_array);
            print_r($parsed_json);
        }

        if (stripos(php_sapi_name(), 'cli') === false) {
            echo '</pre>' . PHP_EOL;
        }
    }
    sleep(1);
} while ($watch = (isset($_GET['w']) ? filter_var($_GET['w'], FILTER_VALIDATE_BOOLEAN) : true));
