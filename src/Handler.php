<?php
declare(strict_types=1);

namespace App;

class Handler
{
    /**
     * @param string $data
     */
    public function handle(string $data)
    {
        $data = json_decode($data);
        $message = null;

        if (!$data['fiscalcode']) {
            $message = 'fiscal code is not present';
        }
        $fiscalCode = strtoupper(trim($data['fiscalcode'])) ?? '';
        if (!$message && (strlen($fiscalCode) != 16 || $this->validateChecksum($fiscalCode) || $this->validateChars($fiscalCode))) {
            $message = 'fiscal code is not valid';
        }
        if (!$message && $data['firstname'] && !$this->validateFirstnameChars($data['firstname'])) {
            $message = 'firstname does not match with fiscal code';
        }
        if (!$message && $data['lastname'] && !$this->validateLastnameChars($data['lastname']))
        {
            $message = 'lastname does not match with fiscal code';
        }
        //@todo: Optionally validate date of birth and gender, but be sure to take into account 'omocodie'
        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/
        if ($message) {
            http_response_code(404);
        } else {
            http_response_code(200);
            $message = 'fiscal code is valid';
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => $message, 'data' => $data]);
    }

    /**
     * @param $fiscalCode string
     * @return bool
     */
    private function validateChecksum(string $fiscalCode): bool
    {
        $set1 = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $set2 = "ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $evenSet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $oddSet = "BAKPLCQDREVOSFTGUHMINJWZYX";
        $sum = 0;
        for ($i = 0; $i < 15; ++$i) {
            $charNoDigits = $set2[strpos($set1, $fiscalCode[$i])];
            if (($i % 2) == 0) {
                $sum += strpos($oddSet, $charNoDigits);
            } else {
                $sum += strpos($evenSet, $charNoDigits);
            }
        }

        return (($sum % 26) == ord($fiscalCode[15])-ord('A'));
    }

    /**
     * @param string $fiscalCode
     * @return bool
     */
    private function validateChars(string $fiscalCode): bool
    {
        $valid = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for ($i = 0; $i < 16; $i++) {
            if (strpos($valid, $fiscalCode[$i]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $fiscalCode string
     * @param $firstname string
     * @return bool
     */
    private function validateFirstnameChars(string $fiscalCode, string $firstname): bool
    {
        $firstname = strtoupper(trim($firstname));

        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/
        $firstnameConsonants = $this->keepConsonants($firstname);
        if (strlen($firstnameConsonants) >= 4) {
            //First, third and fourth consonant
            $firstNameLetters = $firstnameConsonants[0] . substr($firstnameConsonants, 2, 2);
        } else {
            //First three consonants. If not enough consonants, add the vowels. If not anough, pad with X
            $firstNameLetters = substr(($firstnameConsonants . $this->keepVowels($firstname) . 'XXX'), 0, 3);
        }

        return substr($fiscalCode, 3, 3) === $firstNameLetters;
    }

    /**
     * @param $fiscalCode string
     * @param $lastname string
     * @return bool
     */
    private function validateLastnameChars(string $fiscalCode, string $lastname): bool
    {
        $lastname = strtoupper(trim($lastname));

        //First three consonants. If not enough consonants, add the vowels. If not anough, pad with X
        $lastNameLetters = substr(($this->keepConsonants($lastname) . $this->keepVowels($lastname) . 'XXX'), 0, 3);

        return substr($fiscalCode, 0, 3) === $lastNameLetters;
    }

    /**
     * @param $string string
     * @return string
     */
    private function keepConsonants(string $string): string
    {
        return preg_replace('/[^bcdfghjklmnpqrstvwxyz]+/i', '', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    private function keepVowels(string $string): string
    {
        return preg_replace('/[^aeiou]+/i', '', $string);
    }

}
