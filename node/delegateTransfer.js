const TronWeb = require('tronweb');
const tronWeb = new TronWeb({
    fullHost: 'https://api.shasta.trongrid.io',
    // privateKey: 'c4f4ff5b3a45d6279af1ad3f38809bb7a7194b84772a725b5af354cf6887907c'
    // privateKey: '37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913'//test2 account
    privateKey: '4299ba0ed8ca3f35504c84a05b1e9ce993b9df1118f984a6473517117b5ea08d'//test3 account

});

//var abi = {"entrys": [{"constant": true,"name": "isAdmin","inputs": [{"type": "address"}],"outputs": [{"type": "bool"}],"type": "Function","stateMutability": "View"},{"constant": true,"name": "gramGoldCoin","outputs": [{"type": "address"}],"type": "Function","stateMutability": "View"},{"constant": true,"name": "payOutRatio","outputs": [{"type": "uint256"}],"type": "Function","stateMutability": "View"},{"constant": true,"name": "owner","outputs": [{"type": "address"}],"type": "Function","stateMutability": "View"},{"name": "transferOwnership","inputs": [{"name": "_newOwner","type": "address"}],"type": "Function","stateMutability": "Nonpayable"},{"constant": true,"name": "reserveForGame","outputs": [{"type": "uint256"}],"type": "Function","stateMutability": "View"},{"inputs": [{"name": "_admin","type": "address"},{"name": "ggc","type": "address"},{"name": "_payOutRatio","type": "uint256"}],"type": "Constructor","stateMutability": "Nonpayable"},{"name": "OwnershipTransferred","inputs": [{"indexed": true,"name": "previousOwner","type": "address"},{"indexed": true,"name": "newOwner","type": "address"}],"type": "Event"},{"name": "addToAdmin","inputs": [{"name": "admin","type": "address"},{"name": "isAdd","type": "bool"}],"type": "Function","stateMutability": "Nonpayable"},{"name": "setPayOutRatio","inputs": [{"name": "_payOutRatio","type": "uint256"}],"type": "Function","stateMutability": "Nonpayable"},{"name": "withdrawFund","inputs": [{"name": "_amount","type": "uint256"}],"type": "Function","stateMutability": "Nonpayable"},{"name": "startByAdmin","inputs": [{"name": "_player","type": "address"},{"name": "_roundId","type": "uint256"},{"name": "_amount","type": "uint256"},{"name": "_gameSignature","type": "bytes"}],"type": "Function","stateMutability": "Nonpayable"},{"name": "start","inputs": [{"name": "_roundId","type": "uint256"},{"name": "_amount","type": "uint256"}],"type": "Function","stateMutability": "Nonpayable"},{"name": "settle","inputs": [{"name": "_player","type": "address"},{"name": "_roundId","type": "uint256"},{"name": "_payout","type": "uint256"},{"name": "_roundHash","type": "bytes32"},{"name": "_data","type": "string"}],"type": "Function","stateMutability": "Nonpayable"}]};

  // var contractInstance = tronWeb.contract({abi}).at("TULpARDKw4vxamQngQTqGFTLrpkE993s3A");

  // let result = await contractInstance.gramGoldCoin.call();
  
 //  contractInstance.gramGoldCoin.call().then(result => {
	
 //    console.log({result});

 // }).catch(err => console.error(err));

async function testTransfer(){
	//account 1: TXVds7duK34CUavxW4jq2vFA56H9FWXSLE
	//account 2: TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef
	//account 3: TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP
	let contract = await tronWeb.contract().at("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd");

	// contract.approve("TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw",100000000000).send().then(result => {
 //        console.log({result});
 //    }).catch(err => console.error(err));

	// contract.transfer('TFHXab2EqWU3MrjXXuDT3JwDaiXiDWRsoP',10000000000).send().then(result => {
 //        console.log({result});
 //    }).catch(err => console.error(err));


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

async function testContract(){
	//ggc contract : TS8DBxQQ9R996pEURCTuWqeHUvNUEiQcaw
	//game contract : TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd

	let contract = await tronWeb.contract().at("TSg8L8WRxK5bYg6gJTcXS6Y3t2LDQwrgVd");

	// let hex_result = await contract.gramGoldCoin().call();
    
 //    let result = tronWeb.address.fromHex(hex_result);
    

    let result = await contract.reserveForGame().call();

    console.log(result.toString());

    //reserveForGame
	// contract.transfer('TF3xFEjH5xR9LhYz9WTa4GjRnPwHoJFLef',100).send().then(result => {
 //        console.log({result});
 //    }).catch(err => console.error(err));

	
	
};



testTransfer();



