const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'

});


async function withdrawFund(amount){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	let contract = await tronWeb.contract().at("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd");

	// contract.approve("TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw",100000000000).send().then(result => {
 //        console.log({result});
 //    }).catch(err => console.error(err));

	contract.withdrawFund(amount).send().then(result => {
        console.log({result});
    }).catch(err => console.error(err));


	// let result = await contract.balanceOf("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd").call();
	// console.log(result.toString());

	// contract.start(3333,10).send().then(result => {
 //        console.log({result});
 //    }).catch(err => console.error(err));

	// let result = await contract.allowance('TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP','TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw').call();
	// console.log(result.toString());

	// var result = await tronWeb.trx.sendTransaction("TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP", 1000000000);
	// const result = await tronWeb.trx.getBalance("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd");
    // console.log({result});
	

	// console.log(result.toString());
};

let amount = 500000000;
withdrawFund(amount);



