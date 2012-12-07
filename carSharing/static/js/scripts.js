function validate() {
    var err = [];
    var illegal_chars = '~!@#$%^&*()_+=`:;\'"/?[]{}\\|';
    illegal_chars = illegal_chars.split('');
    illegal_chars.forEach(function(c) {
        if ((document.forms['searchform']['from'].value).indexOf(c) != -1) {
            err.push("Illegal character '" + c + " 'in starting location!"); 
        }
        if ((document.forms['searchform']['to'].value).indexOf(c) != -1) {
            err.push("Illegal character '" + c + " 'in ending location!"); 
        }
    });
    if (err.length > 0) {
        console.log(err);
        err = err.join('\n');
        alert(err);
        return false;
    }
}
