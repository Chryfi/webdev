<?php
    /**
     * Functions to read login data from a file.
     */
     class Credentials {
        public static function getDatabasePassword() : ?string {
            $creds = Credentials::readCredentials();
            if ($creds == null
            || !array_key_exists("database", $creds)
            || !array_key_exists("password", $creds["database"])) {
                return null;
            }

            return $creds["database"]["password"];
        }

        public static function getDatabaseUser() : ?string {
            $creds = Credentials::readCredentials();

            if ($creds == null
            || !array_key_exists("database", $creds)
            || !array_key_exists("username", $creds["database"])) {
                return null;
            }

            return $creds["database"]["username"];
        }

        private static function readCredentials() : mixed {
            $contents = file_get_contents(BASE_PATH . "/credentials");
            $json_data = json_decode($contents, true);

            return $json_data;
        }
    }
?>