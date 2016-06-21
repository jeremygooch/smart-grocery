# Smart Grocery
For a working demo see an example [here](http://162.243.20.205/). You can login using 'demo' for both the username and password. Please note this application is intended to be run as a web based application on a [raspberry pi](https://www.raspberrypi.org/) with a 7" touchscreen. An [optional swivel](http://smarticase.com/products/smartipi-touch) can be attached so the device can be mounted in the kitchen. 

## Setting up the OCR Server

1. Install the dependencies. The following instructions (and the basic OCR server) are based off of the great blog article: https://realpython.com/blog/python/setting-up-a-simple-ocr-server/.
```sh
sudo apt-get update
sudo apt-get install autoconf automake libtool
sudo apt-get install libpng12-dev
sudo apt-get install libjpeg62-dev
sudo apt-get install g++
sudo apt-get install libtiff4-dev
sudo apt-get install libopencv-dev libtesseract-dev
sudo apt-get install git
sudo apt-get install cmake
sudo apt-get install build-essential
sudo apt-get install libleptonica-dev
sudo apt-get install liblog4cplus-dev
sudo apt-get install libcurl3-dev
sudo apt-get install python2.7-dev
sudo apt-get install tk8.5 tcl8.5 tk8.5-dev tcl8.5-dev
sudo apt-get build-dep python-imaging --fix-missing
```
Please note, you can install libtiff5-dev if libtiff4-dev is not available in your repos.

2. Install Imagemagick
```sh
sudo apt-get install imagemagick
```

3. Install Leptonica
```sh
cd
wget http://www.leptonica.org/source/leptonica-1.70.tar.gz
tar -zxvf leptonica-1.70.tar.gz
cd leptonica-1.70/
./autobuild
./configure
make
sudo make install
sudo ldconfig
```

4. Install Tesseract
```sh
cd ..
wget https://tesseract-ocr.googlecode.com/files/tesseract-ocr-3.02.02.tar.gz
tar -zxvf tesseract-ocr-3.02.02.tar.gz
cd tesseract-ocr/
./autogen.sh
./configure
make
sudo make install
sudo ldconfig
```

5. Setup an Environment Variable for Tesseract data
```sh
export TESSDATA_PREFIX=/usr/local/share/
```

6. Get the Tesseract English Package
```sh
cd ..
wget https://tesseract-ocr.googlecode.com/files/tesseract-ocr-3.02.eng.tar.gz
tar -xf tesseract-ocr-3.02.eng.tar.gz
sudo cp -r tesseract-ocr/tessdata $TESSDATA_PREFIX
```

7. Clone this repository and cd into it
```sh
sudo apt-get install python-virtualenv
virtualenv env
source env/bin/activate
pip install -r requirements.txt
pip install pytesseract
pip install flask
```

8. Start the OCR Server
```sh
python ocr_server/app.py
```

## Setup the HTTP server
Next, install a typical LAMP stack. PHP version 5.5.x or higher. MySQL version 14.xx or higher. Please note this has only been tested on Ubuntu 14.04 and 15.04.

1. Setup your vhost to point to the correct location for both apache and the OCR server. Update your error and access log file paths. Also, dont forget to update your hosts files.

```xml
## -- Smart Grocery VHost Example -- ##
<VirtualHost *:80>
  ServerAdmin admin@smartgrocery.com
  ServerName local.smartgrocery.com
  ServerAlias www.smartgrocery.com
  DocumentRoot /home/{user}/smart-grocery/
  ErrorLog /home/{user}/smart-grocery/logs/error.log
  CustomLog /home/{user}/smart-grocery/logs/access.log combined
  <Directory />
  Options Indexes FollowSymLinks Includes ExecCGI
  AllowOverride All
  Require all granted
  Allow from all
  </Directory>
</VirtualHost>

## -- Smart Grocery OCR VHost Example -- ##
<VirtualHost *:80>
  ServerAdmin admin@smartgrocery.com
  ServerName local.grocr.com
  ServerAlias local.grocr.com
  DocumentRoot /home/{user}/smart-grocery/ocr_server/
  ErrorLog /home/{user}/smart-grocery/logs/error.log
  CustomLog /home/{user}/smart-grocery/logs/access.log combined
  <Directory />
  Options Indexes FollowSymLinks Includes ExecCGI
  AllowOverride All
  Require all granted
  Allow from all
  </Directory>
</VirtualHost>
```

2. Restart apache for the changes to take effect.

3. Create a new MySQL database and import the template database.
+ Template Database TBD

4. Save the config/example-config.php as config/config.php, and update the placeholder values with your actual values.

5. Start the process to watch for uploaded receipts. In a new terminal, cd in to the project's api folder and run:
```sh
php watch_dir.php
```
6. Install GIMP and the G'MIC plugin
```
sudo add-apt-repository ppa:ferramroberto/gimp
sudo apt-get update
sudo apt-get install gimp
sudo apt-get install gmic gimp-gmic
```

## Test with sample uploaded receipt
Upload image of receipt in portrait mode to the receipts/ directory.
