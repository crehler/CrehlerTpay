#!/usr/bin/env bash

echo -e "\e[92m######################################################################"
echo -e "\e[92m#                                                                    #"
echo -e "\e[92m#                      Start tPay Builder                      #"
echo -e "\e[92m#                                                                    #"
echo -e "\e[92m######################################################################"

echo -e "Release"
echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 1 of 7 \e[33mRemove old release\e[39m"
# Remove old release
rm -rf TpayShopwarePayment TpayShopwarePayment-*.zip
echo -e "\e[32mOK"

echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 2 of 7 \e[33mBuild\e[39m"
cd ../../../
#./bin/build-storefront.sh
#./bin/build-administration.sh
cd custom/static-plugins/tpayshopwarepayment/
echo -e "\e[32mOK"

echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 3 of 7 \e[33mCopy files\e[39m"
rsync -av --progress . TpayShopwarePayment --exclude TpayShopwarePayment
echo -e "\e[32mOK"


echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 4 of 7 \e[33mGo to directory\e[39m"
cd TpayShopwarePayment
echo -e "\e[32mOK"

echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 5 of 7 \e[33mDeleting unnecessary files\e[39m"
cd ..
( find ./TpayShopwarePayment -type d -name ".git" && find ./TpayShopwarePayment -name ".gitignore" && find ./TpayShopwarePayment -name "yarn.lock" && find ./TpayShopwarePayment -name ".php_cs.dist" &&  find ./TpayShopwarePayment -name ".gitmodules" && find ./TpayShopwarePayment -name "build.sh" ) | xargs rm -r
cd TpayShopwarePayment/src/Resources
cd ../../../
echo -e "\e[32mOK"


echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 6 of 7 \e[33mCreate ZIP\e[39m"
zip -rq TpayShopwarePayment-master.zip TpayShopwarePayment
echo -e "\e[32mOK"

echo -e "\e[39m "
echo -e "\e[39m======================================================================"
echo -e "\e[39m "
echo -e "Step 7 of 7 \e[33mClear build directory\e[39m"
rm -rf TpayShopwarePayment
echo -e "\e[32mOK"


echo -e "\e[92m######################################################################"
echo -e "\e[92m#                                                                    #"
echo -e "\e[92m#                        Build Complete                              #"
echo -e "\e[92m#                                                                    #"
echo -e "\e[92m######################################################################"
echo -e "\e[39m "
echo "   _____          _     _           ";
echo "  / ____|        | |   | |          ";
echo " | |     _ __ ___| |__ | | ___ _ __ ";
echo " | |    | '__/ _ \ '_ \| |/ _ \ '__|";
echo " | |____| | |  __/ | | | |  __/ |   ";
echo "  \_____|_|  \___|_| |_|_|\___|_|   ";
echo "                                    ";
echo "                                    ";
