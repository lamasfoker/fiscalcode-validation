<?php
declare(strict_types=1);

namespace LamasFoker\FiscalCodeValidation;

class Handler
{
    public function handle($data)
    {
        return "PHP serverless on Unubo Cloud.";
        if ($_GET['firstname'] && $_GET['lastname'] && $_GET['fiscalcode']) {
            http_response_code(200);
        } else {
            http_response_code(404);
        }
        //@todo: Optionally validate date of birth and gender, but be sure to take into account 'omocodie'
        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/
    }

    /**
     * @param string $fiscalCode
     * @param string|null $firstname
     * @param string|null $lastname
     * @return bool
     */
    private function validateFiscalCodeAgainstName(
        string $fiscalCode,
        string $firstname = null,
        string $lastname = null
    ) {
        $fiscalCode = strtoupper(trim($fiscalCode));

        if (strlen($fiscalCode) != 16) {
            return false;
        }

        $valid = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for ($i = 0; $i < 16; $i++) {
            if (strpos($valid, $fiscalCode[$i]) === false) {
                return false;
            }
        }

        if (!$this->validateChecksum($fiscalCode)) {
            return false;
        }

        if (is_null($firstname) || !$this->validateFirstnameChars($fiscalCode, $firstname)) {
            return false;
        }

        if (is_null($lastname) || !$this->validateLastnameChars($fiscalCode, $lastname)) {
            return false;
        }

        return true;
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
