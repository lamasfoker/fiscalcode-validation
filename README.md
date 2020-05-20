<h1>Fiscal Code Validation API</h1>
This API let you validate an italian fiscal code. More information you gave to it, more accurate will be the validation.
Unubo was been shutted down. So there is no more hosting for this project. See: https://twitter.com/unubo

**Usage**
----

**URL**

  * `https://fiscalcode-validation.unubo.app/`

**Method**

  * `POST`

**Data Params:**

* `fiscalcode` -> required obviously
* `fistname`
* `lastname`
* `birthdate` -> in the format `yyyy/mm/dd`
* `ismale` -> in the format `true|false`
* `municipality` -> the Italian name of the municipality or the foreign country ex:`milano` or `albania`

**Success Response**

  * **Code:** 200 <br />
    **Content:** `{ message : "fiscal code is valid" }`
 
**Error Response**

  * **Code:** 404 NOT FOUND <br />
    **Content:** `{ message : "fiscal code is not valid because..." }`

**Sample Call using <a href="https://httpie.org/">httpie</a>**

 ```shell script
  http post https://fiscalcode-validation.unubo.app/ fiscalcode=rssmra80a01h501u
 ```

 ```shell script
  http post https://fiscalcode-validation.unubo.app/ fiscalcode=rssmra80a01h501u ismale=true birthdate=1980/01/01
 ```
  
**Clarification**
----
This validation is not perfect, it is possible to have **false positive**, because of the presence of the <a href="https://it.wikipedia.org/wiki/Omocodia">omocodie</a>. The only way to know this is to ask to the Agenzia delle Entrate. In its site it is possible to have a <a href="https://telematici.agenziaentrate.gov.it/VerificaCF/Scegli.do?parameter=verificaCf">secure validation</a>. 
