const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'

});


async function mintToken(address,amount){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	//ggc contract 0423 : TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF
	//game contract 0423 : TLciAxFyz54pt7haMDpnDSD8vFjk5hzePR
	let contract = await tronWeb.contract().at("TKuTt2BB6Nh8r18Vq7X8kyUgJf9P2DibQF");

	contract.mintToken(address,amount).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));
};

let mint_to_address = 'TXVds7duK34CUavxW4jq2vFA56H9FWXSLE';
let token_amount = 5000000000;

mintToken(mint_to_address,token_amount);



