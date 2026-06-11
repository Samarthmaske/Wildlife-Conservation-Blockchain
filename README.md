# Animal Wildlife Conservation System with Blockchain

A lightweight web application that records wildlife conservation actions on a blockchain-style ledger. Each conservation report is added as a transaction, then mined into an immutable block.

## Features

- Submit wildlife conservation reports
- Queue reports for mining into blockchain blocks
- Mine pending reports into the ledger on demand
- Verify blockchain integrity
- View mined block history and report details

## Run locally with PHP (XAMPP)

1. Make sure XAMPP is running and `Apache` is started.
2. Place this project under the Apache document root or open it directly if it is already under `c:\xampp\htdocs\Projects\BCTL`.
3. Open your browser to:
   ```
   http://localhost/Projects/BCTL
   ```

The PHP backend is available at `api.php`, and the web app stores conservation reports in a PHP-powered ledger.

## About Solidity

This project still includes `contracts/Conservation.sol` as a smart contract specification, but the current running backend is implemented in PHP.

If you later want to connect to an actual Solidity runtime, you can keep the contract file and deploy it using an Ethereum toolchain separately.
