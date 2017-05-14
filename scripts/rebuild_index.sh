#!/bin/bash

# For CRL and OCSP to function properly an index of all certs needs to be maintained
# the native PHP functions do not update the index when cerificates are issued.

# This script reads contents of all certs to recreate the index

# Basic assumptions.
# 1. Certs will never be deleted
# 2. Certs will end with .crt extension
# 3. Valid Certificates all be in a single folder
# 4. Revoked Certificates will be moved to their own folder.
# 5. The modified timestamp on a revoked certificate is a sufficent revocation time

# Where we keep valid certificates
LIVECERTS="../data/ca/certs/"

# Where we keep revoked certificates
REVOKEDCERTS="../data/ca/revoked/"



# Find Valid Certificates
for cert in `ls -1 ${LIVECERTS}*.crt` 
do

  # Get Expired Date and Format how the index expects YYMMDDHHmmssZ 
  enddate=`openssl x509 -enddate -noout -in $cert | sed 's/notAfter=//' | awk '\
    { year=$4-2000;
      months="JanFebMarAprMayJunJulAugSepOctNovDec" ; 
      month=1+index(months, $1)/3 ; 
      day=$2; 
      hour=substr($3,1,2) ; 
      minutes=substr($3,4,2); 
      seconds=substr($3,7,2); 
      printf "%02d%02d%02d%02d%02d%02dZ", year, month, day, hour, minutes, seconds}'`

  # Get Certificate Serial
  serial=`openssl x509 -serial -noout -in $cert  |sed 's/serial=//'`

  # Get Certificate Subject DN
  subject=`openssl x509 -subject -noout -in $cert  |sed 's/subject= //'`

  echo -e "V\t$enddate\t\t$serial\tunknown\t$subject"
done > /tmp/index.txt


# Find Revoked Certificates
for cert in `ls -1 ${REVOKEDCERTS}*.crt`
do
  
  # Get Revoked Date from modified timestamp and format how the index expects YYMMDDHHmmssZ 
  revdate=`stat $cert --printf '%y' | sed -e 's/[^0-9]*//g'`
  #revdate=`echo $cert | awk -F"_" '{print $1}' | sed -e 's/[^0-9]*//g'`

  
  # Get Expired Date and Format how the index expects YYMMDDHHmmssZ 
  enddate=`openssl x509 -enddate -noout -in $cert | sed 's/notAfter=//' | awk '\
    { year=$4-2000;
      months="JanFebMarAprMayJunJulAugSepOctNovDec" ;
      month=1+index(months, $1)/3 ;
      day=$2;
      hour=substr($3,1,2) ;
      minutes=substr($3,4,2);
      seconds=substr($3,7,2);
      printf "%02d%02d%02d%02d%02d%02dZ", year, month, day, hour, minutes, seconds}'`

  # Get Certificate Serial
  serial=`openssl x509 -serial -noout -in $cert  |sed 's/serial=//'`

  # Get Certificate Subject DN
  subject=`openssl x509 -subject -noout -in $cert  |sed 's/subject= //'`

  # Output Data Tab Seperated as required for index
  echo -e "R\t$enddate\t${revdate:2:12}Z\t$serial\tunknown\t$subject"
  #echo -e "R\t$enddate\t${revdate:-12}Z\t$serial\tunknown\t$subject"
done >> /tmp/index.txt

# Reorder index sorted by Serial Number
cat /tmp/index.txt | awk -F "\t" '{print $4 "\t" $1 "\t" $2 "\t" $3 "\t" $4 "\t" $5 "\t" $6}' | sort | awk -F "\t" '{print $2 "\t" $3 "\t" $4 "\t" $5 "\t" $6 "\t" $7}'
rm /tmp/index.txt
