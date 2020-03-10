<h1>Fiscal Code Validation API</h1>
This API let you validate an italian fiscal code. More information you gave to it, more accurate will be the validation.

**Usage**
----

* **URL**

   `https://fiscalcode-validation.unubo.app/`

* **Method:**

  `POST`

* **Data Params**

   `fiscalcode`<br/>
   `fistname`<br/>
   `lastname`<br/>
   `birthdate` -> in the format `yyyy/mm/dd`<br/>
   `ismale` -> in the format `true|false`<br/>
   `municipality` -> the Italian name of the municipality or the foreign country ex:`milano` or `albania`<br/>

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `{ message : "fiscal code is valid" }`
 
* **Error Response:**

  * **Code:** 404 NOT FOUND <br />
    **Content:** `{ message : "fiscal code is not valid because..." }`

* **Sample Call using <a href="https://httpie.org/" target="_blank">httpie</a>:**

  ```shell script
    http post https://fiscalcode-validation.unubo.app/ fiscalcode=rssmra80a01h501u
  ```
  
**Clarification**
----
This validation is not perfect, it is possible to have false positive and false negative, because of the presence of the <a href="https://it.wikipedia.org/wiki/Omocodia" target="_blank">omocodie</a>. The only way to know this is to ask to the Agenzia delle Entrate. In its site it is possible to have a <a href="https://telematici.agenziaentrate.gov.it/VerificaCF/Scegli.do?parameter=verificaCf" target="_blank">secure validation</a>. 