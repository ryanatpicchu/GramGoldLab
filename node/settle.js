const TronWeb = require('tronweb');
const web3Utils = require('web3-utils');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'//ggc admin

});

async function settle(player, roundId, payout, roundHash, metaData){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
	//ggc contract 0423 : TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF
	//game contract 0423 : TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR
	
	let contract = await tronWeb.contract().at("TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR");

	contract.settle(player,roundId,payout,roundHash,metaData).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));
}


let payout = Number(process.argv[2]);
let roundId = Number(process.argv[3]);
let metaMsg = "You win "+(payout/100000000);


settle("TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef",roundId,payout,web3Utils.asciiToHex(metaMsg),metaMsg);







