const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const _ = require('lodash');

const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});


var privateList = Array();
privateList['TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef'] = '37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913';//account 2

async function startByAdmin(signature,roundId){
	
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
    //ggc contract 0423 : TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF
    //game contract 0423 : TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR
	
	let contract = await tronWeb.contract().at("TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR");

	contract.startByAdmin("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef",roundId,100,signature).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));
}

async function generateDelegateStart(gameAddress,player,roundId,amount){
    let temp = '0x'
    temp += gameAddress.slice(2).toLowerCase();
    temp += player.slice(2).toLowerCase();
    temp += _.padStart(roundId.toString(16), 64, '0');
    temp += _.padStart(amount.toString(16),64,'0')
    let msg = await web3Utils.sha3(temp);
    const sigData =await tronWeb.trx.sign(msg,privateList[tronWeb.address.fromHex(player)]);
    return sigData;
  }


const dateTime = Date.now();
const timestamp = Math.floor(dateTime / 1000);

generateDelegateStart(tronWeb.address.toHex("TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR"),tronWeb.address.toHex("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef"),timestamp,100).then(sig=>{
	console.log(sig);
    console.log(timestamp);
	startByAdmin(sig,timestamp);
});







