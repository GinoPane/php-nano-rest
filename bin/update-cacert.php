<?php

$cacertUrl = "https://curl.haxx.se/ca/cacert.pem";

echo "Trying to update cacert.pem\n";

$updated = @file_get_contents($cacertUrl);

if ($updated === false) {
    echo "Failed to download the cacert.pem";
    exit(1);
}

if (false === file_put_contents(dirname(__FILE__, 2) . "/cacert.pem", $updated)) {
    echo "Failed to store the downloaded cacert.pem";
    exit(1);
};

echo "The cacert.pem was updated successfully\n";

exit(0);
