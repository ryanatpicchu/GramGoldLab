const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io'
});

async function startByUser(){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd
	//ggc contract 0423 : TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF
	//game contract 0423 : TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR

	let contract = await tronWeb.contract().at("TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR");

	contract.start(33333,20).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));

	
};

let userPrivateKey = '4299ba0ed8ca3f35504c84a05b1e9ce993b9df1118f984a6473517117b5ea08d';

tronWeb.setPrivateKey(userPrivateKey);
startByUser();



