var request=require('request');
var sha256 =require('js-sha256').sha256;

function genChecksum(postBody,secret,t){
	var params = [postBody];
	params.push("t="+t);
	params.sort();
	params.push("secret="+secret);
	// console.log(params.join("&"));
	return sha256(params.join("&"));
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
let postBody = '{"signature": "mock_signature","unlock_token": "mock_token","from_address": "mock_from","token_addresss": "mockaddress"}';
let checksum = genChecksum(postBody,myAPISecret,timestamp);


var options = {
	headers: {"X-API-CODE": myAPICode,"X-CHECKSUM": checksum,"Content-Type": "application/json"},
    url: 'https://mvault.cybavo.com/v1/ggc/signsignature'+urlParam,
    method: 'POST',
    json:true,
    body: postBody
};



request(options, callback);