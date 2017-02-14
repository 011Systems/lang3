# lang3

  Localization package from 011systems.
   
## Installation

  ```
  $ npm install --save lang3
  ```
## Hello World Example
  - create ./lang dir in project root
  - in src file index.js
  ```
    import {_} from 'lang3'; 
    _("[ctx] Hello World!")
  ```
  - run command from root dir
  ```
    ./node_modules/.bin/lang.php ./lang/de.js ./src/index.js >> ./lang/de.js     
  ```
  translate strings in  ./lang/de.js
  ```
   "[ctx] Hello World!" : "Hallo Welt"       
  ```
  import lang file "de.js" in your project  
   ```
     <script src="./lang/de.js"></script>     
   ```
## License
### ISC
https://en.wikipedia.org/wiki/ISC_license

