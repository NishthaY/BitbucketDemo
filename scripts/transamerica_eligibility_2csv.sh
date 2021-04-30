#!/bin/bash

help () {
    clear;
    echo "transamerica_eligibility_2csv.sh"
    echo "transamerica_eligibility_2csv.sh </full/path/to/file>"
    echo
    echo "This script will convert an A2P Transamerica Eligibility text file into"
    echo "a CSV file.  If you run this script with no parameters, it will automatically"
    echo "convert the most recently downloaded eligibility report found in your downloads"
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

	# If we find a reserved character, all stop for now.  We will need to add
	# code to handle the reserved character, once we really find one.
	if [[ ${DATUM} = *","* ]]; then
  		echo "Found a reserved character on line number [${LINE_NUMBER}] in column [${LABEL}]";
  		echo "abort."
  		exit;
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


	LABEL="GroupNumberPolicy"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`		
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EmployeeNumber"
	LENGTH=20; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="AddressLine1"
	LENGTH=25; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="AddressLine2"
	LENGTH=25; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="City"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="State"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="ZipCode"
	LENGTH=5; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="ZipPlus4"
	LENGTH=4; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="CountryCode"
	LENGTH=30; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="PhoneNumber"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="IssueState"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="PaidToDate"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"




	# 1 
	LABEL="Relationship-1"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-1"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-1"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-1"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-1"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-1"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-1"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-1"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-1"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	# 2
	LABEL="Relationship-2"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-2"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-2"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-2"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-2"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-2"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-2"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-2"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-2"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#3
	LABEL="Relationship-3"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-3"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-3"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-3"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-3"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-3"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-3"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-3"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-3"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#4
	LABEL="Relationship-4"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-4"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-4"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-4"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-4"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-4"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-4"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-4"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-4"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#5
	LABEL="Relationship-5"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-5"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-5"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-5"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-5"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-5"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-5"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-5"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-5"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#6
	LABEL="Relationship-6"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-6"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-6"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-6"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-6"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-6"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-6"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-6"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-6"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#7
	LABEL="Relationship-7"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-7"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-7"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-7"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-7"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-7"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-7"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-7"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-7"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -7 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#8
	LABEL="Relationship-8"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-8"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-8"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-8"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-8"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-8"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-8"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-8"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-8"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -8 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#9
	LABEL="Relationship-9"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-9"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-9"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-9"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-9"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-9"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-9"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-9"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-9"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -9 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#10
	LABEL="Relationship-10"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-10"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-10"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-10"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-10"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-10"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-10"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-10"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-10"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -10 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#11
	LABEL="Relationship-11"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-11"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-11"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-11"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-11"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-11"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-11"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-11"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-11"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -11 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#12
	LABEL="Relationship-12"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-12"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-12"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-12"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-12"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-12"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-12"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-12"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-12"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -12 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#13
	LABEL="Relationship-13"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-13"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-13"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-13"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-13"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-13"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-13"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-13"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-13"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -13 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#14
	LABEL="Relationship-14"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-14"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-14"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-14"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-14"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-14"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-14"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-14"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-14"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -14 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	#15
	LABEL="Relationship-15"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Status-15"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="FirstName-15"
	LENGTH=15; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="LastName-15"
	LENGTH=19; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MiddleInitial-15"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="EffectiveDate-15"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="TerminationDate-15"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="DateOfBirth-15"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Gender-15"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -15 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"



	LABEL="ProductType"
	LENGTH=1; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Plan"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Tier"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="CreditableCoverage"
	LENGTH=2; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="IndemnityAmount"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="MasterPolicy"
	LENGTH=10; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
	DATUM=`head -${ROW_NUMBER} "${FILE}"|tail -1 | cut -c${STARTS}-${ENDS}`
	add_to_csv ${ROW_NUMBER} "${LABEL}" "${OUTPUT_FILE}" "${OUTPUT_TYPE}" "${DATUM}"

	LABEL="Filler"
	LENGTH=94; STARTS=$((${ENDS} + 1)); ENDS=$(( ${STARTS} + ${LENGTH} - 1));
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

# Get the most recent eligibility report from the download folder.
if [[ ${FILE} == "" ]]; then
    FILE=`ls -1t ~/Downloads/*A2P_*_Transamerica_*_eligibility*.txt | head -1`
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

	if [[ ${ROW_NUMBER} == "1" ]]; then
		HEADER=${LINE}
		convert ${ROW_NUMBER} "${FILE}" "${OUTPUT_FILE}" "LABEL"
	fi 
	if [[ ${ROW_NUMBER} == ${LINE_COUNT} ]]; then
		FOOTER=${LINE}
	fi
	if [[ ${ROW_NUMBER} != "1" && ${ROW_NUMBER} != ${LINE_COUNT} ]]; then		
		convert ${ROW_NUMBER} "${FILE}" "${OUTPUT_FILE}" "DATUM"
	fi	
	ROW_NUMBER=$((${ROW_NUMBER} + 1))
done < "${FILE}"

exit;
