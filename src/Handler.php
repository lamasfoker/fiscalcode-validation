<?php
declare(strict_types=1);

namespace App;

use App\Model\Person;
use App\Model\Person\Loader;

class Handler
{
    /**
     * @var Loader
     */
    private $personLoader;
    /**
     * @var Person
     */
    private $person;
    /**
     * @var string|null
     */
    private $message = null;

    public function __construct()
    {
        $this->personLoader = new Loader();
    }

    /**
     * @param string $data
     */
    public function handle(string $data)
    {
        $this->person = $this->personLoader->load($data);

        if (is_null($this->person->getFiscalCode())) {
            $this->setMessage('fiscal code is not present');
        }
        if (!$this->validateLength()) {
            $this->setMessage('fiscal code length is not valid');
        }
        if (!$this->validateChars()) {
            $this->setMessage('fiscal code has chars not valid');
        }
        if (!$this->validateChecksum()) {
            $this->setMessage('char checksum is not valid');
        }
        if (!$this->validateOmocodie()) {
            $this->setMessage('fiscal code is not valid for omocodie');
        }
        if (!$this->validateFirstNameChars()) {
            $this->setMessage('firstname does not match with fiscal code');
        }
        if (!$this->validateLastNameChars()) {
            $this->setMessage('lastname does not match with fiscal code');
        }
        if (!$this->validateBirthDate()) {
            $this->setMessage('birth date is not correct or it does not match with fiscal code');
        }
        if (!$this->validateGender()) {
            $this->setMessage('gender does not match with fiscal code');
        }
        if (!$this->validateMunicipality()) {
            $this->setMessage('municipality does not match with fiscal code');
        }

        $this->send();
    }

    /**
     * @return bool
     */
    private function validateLength(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        return strlen($this->person->getFiscalCode()) === 16;
    }

    /**
     * @return bool
     */
    private function validateChecksum(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getNotEscapedFiscalCode();
        $set1 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $set2 = 'ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $evenSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $oddSet = 'BAKPLCQDREVOSFTGUHMINJWZYX';
        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $charNoDigits = $set2[strpos($set1, $fiscalCode[$i])];
            if (($i % 2) === 0) {
                $sum += strpos($oddSet, $charNoDigits);
            } else {
                $sum += strpos($evenSet, $charNoDigits);
            }
        }
        return (($sum % 26) === ord($fiscalCode[15])-ord('A'));
    }

    private function validateOmocodie(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getNotEscapedFiscalCode();
        $digit = $fiscalCode[6].$fiscalCode[7].$fiscalCode[9].$fiscalCode[10].$fiscalCode[12].$fiscalCode[13].$fiscalCode[14];
        return preg_match('/^[0-9]*[A-Z]*$/', $digit) === 1;
    }

    /**
     * @return bool
     */
    private function validateChars(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $valid = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < 16; $i++) {
            if (strpos($valid, $fiscalCode[$i]) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    private function validateFirstNameChars(): bool
    {
        if ($this->getMessage() || is_null($this->person->getFirstName())) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $firstName = $this->person->getFirstName();
        $firstNameConsonants = $this->keepConsonants($firstName);
        if (strlen($firstNameConsonants) >= 4) {
            //First, third and fourth consonant
            $firstNameLetters = $firstNameConsonants[0] . substr($firstNameConsonants, 2, 2);
        } else {
            //First three consonants. If not enough consonants, add the vowels. If not enough, pad with X
            $firstNameLetters = substr(($firstNameConsonants . $this->keepVowels($firstName) . 'XXX'), 0, 3);
        }
        return substr($fiscalCode, 3, 3) === $firstNameLetters;
    }

    /**
     * @return bool
     */
    private function validateLastNameChars(): bool
    {
        if ($this->getMessage() || is_null($this->person->getLastName())) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $lastName = $this->person->getLastName();
        //First three consonants. If not enough consonants, add the vowels. If not enough, pad with X
        $lastNameLetters = substr(($this->keepConsonants($lastName) . $this->keepVowels($lastName) . 'XXX'), 0, 3);
        return substr($fiscalCode, 0, 3) === $lastNameLetters;
    }

    /**
     * @return bool
     */
    private function validateBirthDate(): bool
    {
        if ($this->getMessage() || is_null($this->person->getBirthDate())) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $birthDate = $this->person->getBirthDate();
        date_default_timezone_set('Europe/Rome');
        $today = date("Y/m/d");
        $year = substr($birthDate, 2, 2);
        $month = (int)substr($birthDate, 5, 2);
        $day = substr($birthDate, 8, 2);
        $monthArrayMap = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'H',
            7 => 'L',
            8 => 'M',
            9 => 'P',
            10 => 'R',
            11 => 'S',
            12 => 'T',
        ];
        if ($year !== substr($fiscalCode, 6, 2)) {
            return false;
        }
        if (!array_key_exists($month, $monthArrayMap) || $monthArrayMap[$month] !== substr($fiscalCode, 8, 1)) {
            return false;
        }
        if ($birthDate > $today) {
            return false;
        }
        return $day === substr($fiscalCode, 9, 2);
    }

    /**
     * @return bool
     */
    private function validateGender(): bool
    {
        if ($this->getMessage() || is_null($this->person->getIsMale())) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $day = (int)substr($fiscalCode, 9, 2);
        if (!$this->person->getIsMale()) {
            $day -= 40;
        }
        return $day > 0 && $day < 32;
    }

    private function validateMunicipality()
    {
        if ($this->getMessage() || is_null($this->person->getMunicipality())) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $code = substr($fiscalCode, 11, 4);
        $municipality = $this->person->getMunicipality();
        if (!preg_match('/^[A-Z \']+$/', $municipality) || !preg_match('/^[A-Z][0-9]{3}$/', $code)) {
            return false;
        }
        if (preg_match('/^Z([0-9]{3})$/', $code, $countryCode)) {
            //stranger or born in a foreign country
            //it is possible to use a CSV from the official site, but I am lazy and I should have to update it
            //see: https://sister.agenziaentrate.gov.it/CitizenArCom/InitForm.do?ric=report
            $url = 'https://www.ilcodicefiscale.it/codici-catastali-comunali.php';
            $query = http_build_query(['comune' => $municipality]);
            $body = file_get_contents($url . '?' . $query);
            if (preg_match('/<h3>(Z[0-9]{3})<\/h3>/i', $body, $codeChecked)) {
                return $code === trim($codeChecked[1]);
            }
        } else {
            $url = 'https://www1.agenziaentrate.gov.it/documentazione/versamenti/codici/ricerca/VisualizzaTabella.php';
            $query = http_build_query([
                'iniz' => substr($municipality, 0, 1),
                'ArcName' => 'COM-ICI'
            ]);
            $body = file_get_contents($url . '?' . $query);
            if (preg_match('/([A-Z][0-9]{3})<\/td><td >' . $municipality . '/i', $body, $codeChecked)) {
                return $code === trim($codeChecked[1]);
            }
        }
        return false;
    }

    /**
     * @param $string string
     * @return string
     */
    private function keepConsonants(string $string): string
    {
        return preg_replace('/[^BCDFGHJKLMNPQRSTVWXYZ]+/', '', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    private function keepVowels(string $string): string
    {
        return preg_replace('/[^AEIOU]+/', '', $string);
    }

    /**
     * @return string|null
     */
    private function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    private function setMessage(string $message): void
    {
        $this->message = $message;
    }

    private function send()
    {
        if ($this->getMessage()) {
            http_response_code(404);
        } elseif ($this->person->getFiscalCode() !== $this->person->getNotEscapedFiscalCode()) {
            http_response_code(200);
            $this->setMessage('fiscal code present omocodie and can be not valid');
        } else {
            http_response_code(200);
            $this->setMessage('fiscal code is valid');
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => $this->getMessage()]);
    }
}
