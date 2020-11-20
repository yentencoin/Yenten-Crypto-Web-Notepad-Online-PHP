# Yenten Crypto Web Notepad Online PHP

**Features:**
- works in one php file and works on almost any hosting 
- you can work with 6 online notebooks, quickly switch between them
- an encrypted file is stored on the hosting, i.e. if the files are leaked, they will not be opened and what is inside will not be read. Decryption occurs with a key (it is also the second password) when opening the file and encrypted with it when saving. 
- the first password is to close access to the folder by password (.htpasswd on folder)
- automatic saving of the file when typing (it monitors that there were pressing or changes every 5 seconds and, if necessary, saves)
- manual saving by pressing the button
- manual backup by button (the ability to save to a remote FTP server)
- automatic backup (1, 7, 30 days)
- lightweight, small, opens instantly in almost any browser

*Why did I do it at all: I didn't want to store my data with someone, but access to them is needed from different devices. And here everything is reliable, hosting is your own, files are encrypted + backups, you can make these files by simply saving them to your hard drive. For myself - a convenient thing.*

**Online test:** http://yenten.nichesite.org/notepad/1.php

login: 123
pass: 123
pass2: 123


**Setup:**
*(tests on php 7.4)*
 - download latest release - https://github.com/yentencoin/Yenten-Crypto-Web-Notepad-Online-PHP/releases
 - unzip
 - create new folder on server and password protect (sample: https://stackoverflow.com/questions/5229656/password-protecting-a-directory-and-all-of-its-subfolders-using-htaccess)
 - upload files to server
 - open 1.php file
 - enter pass (the first time you enter a password, files will be encrypted with this password)
