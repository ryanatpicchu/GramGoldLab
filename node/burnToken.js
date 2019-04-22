const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'

});


async function burnToken(burnedAmount){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	let contract = await tronWeb.contract().at("TW5oTxxwTzNFv7EbiSy7FEKN3qAeFLF3N1");

	contract.burnToken(burnedAmount).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));
};

let burned_amount = 3000000000;

burnToken(burned_amount);



