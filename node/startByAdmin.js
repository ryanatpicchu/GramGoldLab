const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const _ = require('lodash');

const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

var privateList = Array();
privateList['TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef'] = '37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913';//account 2

async function startByAdmin(signature,roundId,amount){
	
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
	
	let contract = await tronWeb.contract().at("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd");

	contract.startByAdmin("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef",roundId,amount,signature).send().then(result => {
        console.log(result);

     //    tronWeb.trx.getTransaction(result).then(
	    //     tranResult=>{
	    //     	console.log(tranResult);
	    //     }
	    // ).catch(err=>console.error(err));
        

    }).catch(err => console.error(err));
}

async function generateDelegateStart(gameAddress,player,roundId,amount){
    let temp = '0x'
    temp += gameAddress.slice(2).toLowerCase();
    temp += player.slice(2).toLowerCase();
    temp += _.padStart(roundId.toString(16), 64, '0');
    temp += _.padStart(amount.toString(16),64,'0')
    let msg = await web3Utils.sha3(temp);
    const sigData = await tronWeb.trx.sign(msg,privateList[tronWeb.address.fromHex(player)]);
    return sigData;
  }

let amount = Number(process.argv[2]);
let roundId = Number(process.argv[3]);

// const dateTime = Date.now();
// const roundId = Math.floor(dateTime / 1000);

generateDelegateStart(tronWeb.address.toHex("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd"),tronWeb.address.toHex("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef"),roundId,amount).then(sig=>{
	// console.log(amount);
	// console.log(roundId);
	startByAdmin(sig,roundId,amount);
});







