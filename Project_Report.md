# Project Report: Animal Wildlife Conservation System

## INDEX
| Sr. No. | Topic | Page Number |
| :--- | :--- | :--- |
| **1.** | **Abstract** | **1** |
| **2.** | **Introduction** <br> a. Need of the Work <br> b. Proposed System | **2** |
| **3.** | **Problem Statement & Objectives** | **4** |
| **4.** | **Requirement Analysis** <br> a. Hardware Requirements <br> b. Software Requirements <br> c. Libraries Used | **6** |
| **5.** | **System Design & Implementation** <br> a. System Architecture <br> b. Implementation Details | **10** |
| **6.** | **Result (Sequential Screen Shots)** | **12** |
| **7.** | **Conclusion** | **14** |
| **8.** | **References** | **15** |

<div style="page-break-after: always;"></div>

## 1. Abstract
The preservation of animal wildlife and the management of their habitats are critical environmental imperatives in the modern era. As the threats of poaching, habitat destruction, and climate change escalate, the need for accurate, transparent, and immutable data tracking becomes paramount. The Animal Wildlife Conservation System is a comprehensive, lightweight web application explicitly designed to address these challenges by recording wildlife conservation actions on a blockchain-style ledger.

By treating each recorded conservation event—such as animal rescues, medical interventions, species releases, and habitat restorations—as an independent transaction, the system guarantees data integrity. These transactions are queued and subsequently mined into an immutable block, cryptographically linked to the preceding block. The application offers a highly intuitive, user-friendly interface for field officers, researchers, and Non-Governmental Organizations (NGOs) to submit real-time reports directly from the field. 

Furthermore, these reports are stored securely and chained together using SHA-256 hashing algorithms, thereby preventing any retrospective tampering or unauthorized modification of historical data. Ultimately, this project demonstrates how decentralized ledger principles, traditionally associated with cryptocurrency, can be effectively applied to real-world conservation tracking. This paradigm shift promotes unparalleled transparency, accountability, and trust among various stakeholders involved in global wildlife conservation efforts.

<div style="page-break-after: always;"></div>

## 2. Introduction

### a. Need of the Work
In the critical field of wildlife conservation, maintaining accurate, transparent, and indisputable records of actions is vital for both strategic planning and accountability. Billions of dollars are allocated globally to environmental protection, yet the data management systems tracking the efficacy of these efforts often rely on conventional, centralized databases. These traditional systems present several severe vulnerabilities. They represent a single point of failure and are highly susceptible to data manipulation, accidental deletion, internal database corruption, or malicious alterations by unauthorized personnel. 

Furthermore, when multiple independent entities—such as government wildlife departments, international NGOs, and local field officers—collaborate, establishing a single source of truth is incredibly difficult. Trust issues frequently arise regarding the authenticity of field reports and the exact allocation of conservation resources. To establish verifiable trust among all participating bodies and the general public, there is a pressing, critical need for a system where once a conservation action is permanently recorded, it cannot be tampered with, deleted, or retrospectively altered under any circumstances.

### b. Proposed System
The proposed Animal Wildlife Conservation System is an innovative PHP-powered web application that integrates core blockchain mechanics to secure vital conservation data. Unlike traditional systems, it allows authorized users (field officers, veterinarians) to submit detailed conservation reports which are initially placed in a pending transaction queue. 

System administrators or authorized nodes can then "mine" these pending reports, bundling them into verified blocks that are permanently appended to an immutable digital ledger. Each block contains a unique cryptographic hash and the exact hash of the previous block, creating an unbreakable chain. Any attempt to modify a historical record would instantly invalidate the cryptographic hash of that block and all subsequent blocks, alerting administrators to the breach. The backend efficiently handles these cryptographic operations and data persistence via a structured MySQL database, while a responsive, modern frontend provides a seamless, cross-platform reporting experience for end-users.

<div style="page-break-after: always;"></div>

## 3. Problem Statement & Objectives

### Problem Statement
Wildlife conservation efforts generate massive volumes of critical field data that are currently stored in highly fragmented, centralized legacy systems lacking inherent verifiability and transparency. This architectural flaw makes it exceedingly difficult to conduct objective audits of conservation actions, verify the authenticity and timeline of reports generated by field officers, and ensure that sensitive data regarding endangered species populations or critical habitat locations has not been manipulated for political or financial gain. There is a lack of a unified, immutable platform that guarantees the integrity of ecological data from the moment it is recorded.

### Objectives
To directly address the issues outlined in the problem statement, this project aims to achieve the following core objectives:
1. **Design a Transparent Reporting Interface:** Create a clean, accessible, and responsive web interface that allows field officers to quickly and easily submit detailed conservation reports without requiring extensive technical training.
2. **Implement an Immutable Digital Ledger:** Develop and deploy a custom, lightweight blockchain data structure using PHP to securely chain conservation reports sequentially over time.
3. **Ensure Cryptographic Data Integrity:** Utilize industry-standard SHA-256 hashing algorithms to link blocks together, providing a built-in verification mechanism to audit the entire chain's integrity instantly.
4. **Robust Data Persistence and Management:** Implement a reliable backend architecture utilizing MySQL to handle relational data storage (like animal profiles and user data) alongside the immutable ledger records.
5. **Facilitate Multi-Stakeholder Trust:** Create a system architecture that can act as a single, verifiable source of truth for independent NGOs, governmental bodies, and researchers.

<div style="page-break-after: always;"></div>

## 4. Requirement Analysis

### a. Hardware Requirements
To ensure optimal performance and reliability for the web application and its database, the following hardware specifications are recommended:
- **Processor:** Minimum Dual-core 2.0 GHz (e.g., Intel Core i3 / AMD Ryzen 3). Recommended: Quad-core processor for handling concurrent cryptographic hashing during the mining process.
- **RAM:** Minimum 4 GB. Recommended: 8 GB to ensure smooth database query execution and caching.
- **Storage:** Minimum 20 GB of available solid-state drive (SSD) storage for fast read/write operations of the blockchain ledger and associated database logs.
- **Network:** A stable, active internet or localized intranet connection with a minimum bandwidth of 10 Mbps for remote reporting and synchronization.

### b. Software Requirements
The system is built on a universally accessible and open-source software stack to ensure ease of deployment:
- **Operating System:** Cross-platform compatibility (Windows 10/11, Ubuntu Linux 20.04+, or macOS).
- **Web Server Environment:** XAMPP stack or an equivalent standalone Apache HTTP Server (Version 2.4+).
- **Database Management System:** MySQL Server (Version 8.0+ or MariaDB equivalent) bundled with XAMPP for robust relational data handling.
- **Scripting Language:** PHP 7.4 or PHP 8.x for backend logic, API endpoint generation, and cryptographic hashing.
- **Client Software:** Any modern HTML5-compliant Web Browser (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari).

### c. Libraries Used
To keep the application highly lightweight and secure, it relies heavily on native functionalities rather than bloated third-party frameworks:
- **Frontend Libraries:** 
  - Native Vanilla JavaScript (`ES6+`) for DOM manipulation.
  - Fetch API for asynchronous HTTP requests to the backend.
  - CSS3 with modern CSS Grid and Flexbox for responsive, adaptive styling.
- **Backend Libraries:** 
  - Native PHP core functions.
  - Custom PHP Object-Oriented classes (`Block.php`, `Blockchain.php`).
- **Database Connection:** 
  - PHP Data Objects (PDO) extension for secure, prepared MySQL queries, actively preventing SQL injection attacks.

<div style="page-break-after: always;"></div>

## 5. System Design & Implementation

### a. System Architecture
The system employs a sophisticated 3-tier architecture seamlessly integrated with a custom blockchain logic layer to ensure data security and separation of concerns:
1. **Presentation Layer (Frontend/Client-Side):** Composed of clean HTML5, structured CSS3, and asynchronous JavaScript. It serves as the primary interface for users. It provides interactive forms for report submission and dynamic dashboards that display the real-time status of the ledger and pending transactions.
2. **Application Layer (Backend API/Server-Side):** Implemented in modular PHP (`api.php`, `db-api.php`, and dedicated class files). This layer acts as the brain of the application. It handles all critical business logic, sanitizes and processes incoming reports, validates data integrity, and executes the proof-of-work (or simplified mining algorithm) required to generate new cryptographic blocks.
3. **Data Layer (Database & Immutable Ledger):** MySQL serves as the persistent storage engine. It maintains strict relational tables for `animals`, `conservation_actions`, `habitats`, and the `blockchain_ledger`. The ledger table is uniquely designed to store the immutable block hashes, timestamps, and serialized transaction data permanently.

### b. Implementation Details
- **Blockchain Mechanics and Hashing Strategy:** The application temporarily queues incoming conservation reports as "pending transactions". When an authorized user triggers the "Mine Pending Reports" function, the PHP backend instantiates a new `Block` object. This block encapsulates all pending transactions. The system then calculates a unique SHA-256 hash for this new block by combining its timestamp, transaction data, and crucially, the exact `hash` of the preceding block (`previous_hash`). This chaining is what renders the data immutable.
- **Database Integration and Schema (`db-api.php`):** The system connects securely to the `bctl_animal_db` database using PHP Data Objects (PDO). Specialized REST-like API endpoints (`addAnimal`, `addAction`, `getAnimals`, `statistics`) handle all standard CRUD (Create, Read, Update, Delete) operations for the non-blockchain data, ensuring that the relational data remains synchronized with the ledger.
- **Frontend Interaction and User Experience (`index.php` & `app.js`):** The user interface is designed to be highly responsive. It leverages the JavaScript Fetch API to make asynchronous GET and POST requests to the PHP backend. This allows the UI to dynamically update the pending report count, submit forms without page reloads, and visually render the blockchain ledger in real-time as new blocks are successfully mined and validated.

<div style="page-break-after: always;"></div>

## 6. Result (Sequential Screen Shots)

The application provides a seamless visual flow from data entry to permanent, immutable storage. Below are detailed screenshots demonstrating the primary interfaces of the system.

**1. Submitting a Conservation Report**
The core data entry point for field officers. The user fills out comprehensive details regarding the specific animal species, the geographical location of the event, the type of habitat, and the specific conservation action taken (e.g., Rescue, Patrol, Education). Once submitted, the system captures this data as a pending transaction.

![Submit Conservation Report Form](C:\Users\Samarth P. Maske\.gemini\antigravity\brain\fcdd652f-22c2-40d1-839e-e30b037e208a\conservation_form_1778390942819.png)

**2. Ledger Controls and On-Chain View**
This administrative interface allows for the management of the blockchain. After reports are queued, authorized users can click "Mine Pending Reports" to permanently write them to the blockchain. The "On-Chain Conservation Ledger" view below the controls displays a real-time, chronological list of immutable blocks, publicly showcasing the transaction details, the exact timestamp of mining, and the cryptographic block hash.

![Blockchain Ledger View](C:\Users\Samarth P. Maske\.gemini\antigravity\brain\fcdd652f-22c2-40d1-839e-e30b037e208a\ledger_view_1778391048713.png)

<div style="page-break-after: always;"></div>

## 7. Conclusion
The Animal Wildlife Conservation System successfully illustrates the profound, practical applications of blockchain technology far beyond the realm of cryptocurrency. By transitioning from a standard, easily modifiable centralized database paradigm to an immutable, cryptographically secure digital ledger, this project guarantees that highly sensitive and critical conservation data—such as endangered animal rescues, medical interventions, and habitat restorations—are preserved with absolute, undeniable integrity. 

This inherent transparency effectively eliminates the possibility of data manipulation, fostering an unprecedented level of trust and accountability among field officers, governmental agencies, and international wildlife organizations. The project proves conclusively that even a lightweight, customized blockchain implementation integrated with traditional web technologies (PHP/MySQL) can significantly enhance data security and auditability in specialized, high-stakes domains like environmental protection. Future iterations of this system could easily expand to integrate with public smart contract networks (such as Ethereum) to further decentralize the data and allow for global, public auditing of conservation milestones.

<div style="page-break-after: always;"></div>

## 8. References
1. *PHP Official Documentation*. PHP: Hypertext Preprocessor. Available at: https://www.php.net/docs.php (Accessed May 2026).
2. *MySQL 8.0 Reference Manual*. Oracle Corporation. Available at: https://dev.mysql.com/doc/refman/8.0/en/ (Accessed May 2026).
3. Nakamoto, S. (2008). *Bitcoin: A Peer-to-Peer Electronic Cash System*. Available at: https://bitcoin.org/bitcoin.pdf (Primary source for core blockchain chaining concepts).
4. *MDN Web Docs - Fetch API*. Mozilla Foundation. Available at: https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API (Reference for asynchronous web architecture).
5. Swan, M. (2015). *Blockchain: Blueprint for a New Economy*. O'Reilly Media, Inc. (Reference for decentralized applications and non-financial blockchain uses).
6. World Wildlife Fund (WWF). *Living Planet Report 2024*. Available at: https://www.worldwildlife.org/ (Reference for current statistics on the critical need for wildlife conservation data tracking).
