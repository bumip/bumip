// Make sure we got a filename on the command line.
if (process.argv.length < 3) {
    console.log('Usage: node ' + process.argv[1] + ' FILENAME');
    process.exit(1);
}
// Read the file and print its contents.
var fs = require('fs')
    , filename = process.argv[2];
fs.readFile(filename, 'utf8', function (err, data) {
    if (err) throw err;
    let d = data.replace("__", "[[PROTECTED]]").replace(">_", "[[PROTECTED2]]").replace(" _", "[[PROTECTED3]]")
    let dArr = d.split("_");
    for (let index = 0; index < dArr.length; index++) {
        const el = dArr[index];
        if (index % 2) {
            dArr[index] = el.charAt(0).toUpperCase() + el.slice(1)

            // var firstLetter = el[0];
            // console.log(firstLetter)
            // firs
        }
    }
    d = dArr.join("").replace("[[PROTECTED]]", "__").replace("[[PROTECTED2]]", ">_").replace("[[PROTECTED3]]", " _");
    fs.writeFile(filename, d, function (err) {

    });

});