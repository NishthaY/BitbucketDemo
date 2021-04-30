#!/bin/bash

help () {
    clear;
    echo "transamerica_commissions_2csv.sh"
    echo "transamerica_commissions_2csv.sh </full/path/to/file>"
    echo
    echo "This script will convert an A2P Transamerica Commissions text file into"
    echo "a CSV file.  If you run this script with no parameters, it will automatically"
    echo "convert the most recently downloaded commissions report found in your downloads"
    echo "folder on your Mac.  If you do not have a Mac, or want to transform a specific"
    echo "file, then provide the full path and filename to the file you want to convert."
    echo "In either case, the CSV file generated will live next to the file it converted."
    echo "The file will be named the same as the original file, but with the CSV extension."
    echo 
    echo "NOTE: The auto-selecting file feature assumes A2P file format."
    echo 
	echo "Press any key to continue or <ctrl>-C to exit."
	read varname
}

add_to_csv () {

	LINE_NUMBER=$1
	LABEL=$2	
	OUTPUT_FILE=$3
	OUTPUT_TYPE=$4	
	DATUM=$5

	# If we see a comma in the field, wrap it in double quotes.
	if [[ ${DATUM} = *","* ]]; then
	    DATUM="\"${DATUM}\""
	fi

	if [[ ${OUTPUT_TYPE} != "LABEL" ]]; then
		echo -n ${DATUM}, >> "${OUTPUT_FILE}"
	fi
	if [[ ${OUTPUT_TYPE} == "LABEL" ]]; then
		echo -n ${LABEL}, >> "${OUTPUT_FILE}"
	fi
	
	
}
convert(){

	ROW_NUMBER=$1

	FILE=$2		
	OUTPUT_FILE=$3
	OUTPUT_TYPE=$4

	# This is imporant!  Clear these out each time you run.
	STARTS=
	ENDS=


	LABEL="MasterPolicy"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`		
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EmployeeID"
	LENGTH=16; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TierEffectiveDate"
	LENGTH=8; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Tier"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TierMonthlyPremium"
	LENGTH=7; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="CurrentCertStatus"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="OriginalCertIssueDate"
	LENGTH=8; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="CertTermDate"
	LENGTH=8; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MonthPaidFor"
	LENGTH=8; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EmployeeName"
	LENGTH=40; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="PremiumFirstYear"
	LENGTH=7; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="PremiumRenewal"
	LENGTH=7; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	echo -n "."
	echo $'\r' >> "${OUTPUT_FILE}"
	
}

#+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
#START OF PROGRAM
#+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

FILE=$1
ENDS=0

# Get the most recent commissions report from the download folder.
if [[ ${FILE} == "" ]]; then
    FILE=`ls -1t ~/Downloads/*A2P_*_Transamerica_*_commission*.txt | head -1`
fi

# Create an identical file, right next to the original, that 
# ends in .csv.
OUTPUT_FILE=`echo ${FILE} | sed 's/.txt/.csv/g'`
> "${OUTPUT_FILE}"


help
echo "Processing File ..."
echo "READING: [${FILE}]"
echo "WRITING: [${OUTPUT_FILE}]"


#Init the ROW_NUMBER
ROW_NUMBER="$(echo -e "${ROW_NUMBER}" | tr -d '[:space:]')"
ROW_NUMBER=$((${ROW_NUMBER} + 1))

# How long is the file.
LINE_COUNT=`cat "${FILE}" | wc -l`
LINE_COUNT=$((${LINE_COUNT} + 0))

while read LINE; do

    # There are no headers and footers in this file.

    # Convert the row.
	convert ${ROW_NUMBER} "${FILE}" "${OUTPUT_FILE}" "DATUM"

	# increment our counter.
	ROW_NUMBER=$((${ROW_NUMBER} + 1))
	
done < "${FILE}"

exit;
