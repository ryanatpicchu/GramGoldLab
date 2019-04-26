const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const _ = require('lodash');

const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});


var request=require('request');
var sha256 =require('js-sha256').sha256;
var buffer=require('buffer').Buffer;

async function genChecksum(postBody,secret,t){
	console.log(postBody);
	var params = [postBody];
	params.push("t="+t);
	params.sort();
	params.push("secret="+secret);
	// console.log(params.join("&"));
	return sha256(params.join("&"));
}

async function generateSignatureMsg(gameAddress,player,roundId,amount){
    let temp = '0x'
    temp += gameAddress.slice(2).toLowerCase();
    temp += player.slice(2).toLowerCase();
    temp += _.padStart(roundId.toString(16), 64, '0');
    temp += _.padStart(amount.toString(16),64,'0')
    let msg = await web3Utils.sha3(temp);
    // console.log(msg);
    msg = new buffer(msg).toString('base64');


    // console.log(msg);
    return msg;
}

function callback(error, response, data) {
    if (!error && response.statusCode == 200) {
        console.log('----info------',data);
    }
    else{
    	console.log('----info------',data);
    }
}

const dateTime = Date.now();
const timestamp = Math.floor(dateTime / 1000);

const myAPICode = "eTuA1iogiWDCp8ij";
const myAPISecret = "2hXBK5qRcfKx2ApXo1f3B8eru7qd";

let urlParam = '?t='+timestamp;

generateSignatureMsg(tronWeb.address.toHex("TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR"),tronWeb.address.toHex("TFgWpZy4Jg2yeFCyBAfJV7ZM8tVezDRsau"),timestamp,1000000000).then(mockSignature => {

	let postBody = {"signature": mockSignature,"unlock_token": "641azkvvmPHetotUBSeDt4g6qp8zF44XXqpV1x1jeXfF","from_address": "TAVQVeCSVX43pzu2BTHrgsLLTaLkvQxxcD","token_addresss": "TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF"};
	let bodyString = JSON.stringify(postBody);

	genChecksum(bodyString,myAPISecret,timestamp).then(checksum=>{

		var options = {
			headers: {"X-API-CODE": myAPICode,"X-CHECKSUM": checksum,"Content-Type": "application/json"},
		    url: 'https://mvault.cybavo.com/v1/ggc/signsignature'+urlParam,
		    method: 'POST',
		    json:false,
		    body: bodyString
		};

		console.log(options);

		request(options, callback);	

	});


	
});

