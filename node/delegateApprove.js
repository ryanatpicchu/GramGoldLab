const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const _ = require('lodash');

const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});


var privateList = Array();
privateList['TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef'] = '37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913';//account 2

async function delegateApprove(signature){
	console.log(signature);
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
	
	let contract = await tronWeb.contract().at("TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw");

	contract.delegateApprove("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef","TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd",500000000000000,signature,1233).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));
}

// async function ____generateDelegateApprove(tokenAddress,sender,from,spender,value,nonce){
//     let temp = '0x';
//     temp +=  String(tronWeb.address.toHex(tokenAddress)).substr(2);//this

//     temp += 'd086acd8';//msg.sig
//     temp += String(tronWeb.address.toHex(sender)).substr(2);//msg.sender
//     temp += String(tronWeb.address.toHex(from)).substr(2);//sender
//     temp += String(tronWeb.address.toHex(spender)).substr(2);;//spender
//     temp += _.padStart(value.toString(16), 64, '0');
//     temp += _.padStart(nonce.toString(16), 64, '0');
    
//     let msg = await web3Utils.sha3(temp);


//     const sigData = await tronWeb.trx.sign(msg,privateList[from]);
    
//     return sigData;
// }

async function generateDelegateApprove(tokenAddress,sender,from,spender,value,nonce){
    let temp = '0x'
    temp +=  tokenAddress.slice(2).toLowerCase();
    temp += 'd086acd8';
    temp += sender.slice(2).toLowerCase();
    temp += from.slice(2).toLowerCase();
    temp += spender.slice(2).toLowerCase();
    temp += _.padStart(value.toString(16), 64, '0');
    temp += _.padStart(nonce.toString(16),64,'0')
    let msg = await web3Utils.sha3(temp);
    const sigData =await tronWeb.trx.sign(msg,privateList[tronWeb.address.fromHex(from)]);
    return sigData;
  }


generateDelegateApprove(tronWeb.address.toHex("TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw"),tronWeb.address.toHex("TXVds7duK34CUavxW4jq2vFA56H9FWXSLE"),tronWeb.address.toHex("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef"),tronWeb.address.toHex("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd"),500000000000000,1233).then(sig=>{
	console.log(sig);
	delegateApprove(sig);
});







