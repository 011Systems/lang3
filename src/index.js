/** @fileoverview index.js

 TODO:

 DONE:

 =============================================================================*/
let IS_DEV = typeof process !== "undefined" || // node
  (document && document.URL.indexOf('localhost/')>=0) ||
  (document && document.URL.indexOf('10.1.1.27/')>=0);

// do any init here ...


  /**
 @param {string} str
 @param {string=} key
 @return {string}
 */
export function _(str,key) {
  // This matches PHP version in session.php
  // str: "[ctx] orig"
  var XLT=window['XLT'];
  var re=/^\[[^\[]*\] */;

  if(typeof(XLT)==="undefined") { // No XLT translation table
    if(str.charAt(0)==='[') str=str.replace(re,'');
    return str;
  }

  var x=XLT[str];
  if(!x) { // XLT string is NULL or empty
    if(str.charAt(0)!=='[') // no [ctx]: return orig string
      return tt.DEV?('[#'+str+'#]'):str;
    str=str.replace(re,'');
    x=XLT[str];
  }

  if(!x) { // XLT string NULL or empty: return orig string
    if(str.charAt(0)==='[') str=str.replace(re,'');
    return tt.DEV?('[#'+str+'#]'):str;
  }

  if(typeof(x)==='string') {
    if(x.charAt(0)==='[') x=x.replace(re,'');
    return x;
  }

  if(typeof(x)==='object') { // Keyed translation
    var y=x[key];
    if(!y) y=x['default'];
    if(y) {
      if(y.charAt(0)==='[') y=y.replace(re,'');
      return y;
    }
  }

  if(str.charAt(0)==='[') str=str.replace(re,'');
  return str;
}
// EOF
