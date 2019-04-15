const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const _ = require('lodash');

const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});


async function getTransactionInfo(txID){
	
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
	
	tronWeb.trx.getTransaction(txID).then(
        tranResult=>{
            console.log(tranResult);
        }
    ).catch(err=>console.error(err));
}

let txID = process.argv[2];

getTransactionInfo(txID);







