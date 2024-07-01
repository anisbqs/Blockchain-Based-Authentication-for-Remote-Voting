module.exports = {
  networks: {
    development: {
      host: "127.0.0.1",     // Localhost (default: none)
      port: 7545,            // Standard Ethereum port for Ganache
      network_id: "5777",    // Network ID for Ganache
      gas: 6721975,          // Gas limit
      gasPrice: 20000000000  // 20 gwei (in wei)
    },
  },
  // Configure your compilers
  compilers: {
    solc: {
      version: "0.8.19"    // Specify the downgraded compiler version
    }
  }
};
