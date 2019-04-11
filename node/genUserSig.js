const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io'
});

let userPrivkey = String(process.argv.slice(2));
let signMessage = tronWeb.toHex("signaturfordelegate");

tronWeb.trx.sign(signMessage, "0x37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913").then(signature => {
	
    console.log({signature});

}).catch(err => console.error(err));
