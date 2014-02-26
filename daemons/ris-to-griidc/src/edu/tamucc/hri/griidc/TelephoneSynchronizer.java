package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Arrays;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.TableColInfo;

public class TelephoneSynchronizer {

	private static final String TableName = "Telephone";
	private static final String TelephoneKeyColName = "Telephone_Key";
	private static final String CountryNumberColName = "Country_Number";
	private static final String TelephoneNumberColName = "Telephone_Number";

	private static boolean Debug = false;

	private String msg = null;

	private static TelephoneSynchronizer instance = null;

	private int griidcRecordsAdded = 0;
	private int griidcDuplicates = 0;
	private int risTelephoneRecords = 0;
	private int risTelephoneErrors = 0;
	public static final int NotFound = -1;

	private TelephoneSynchronizer() {

	}

	public static TelephoneSynchronizer getInstance() {
		if (TelephoneSynchronizer.instance == null) {
			TelephoneSynchronizer.instance = new TelephoneSynchronizer();
		}
		return TelephoneSynchronizer.instance;
	}

	/**
	 * If the Telephone table does not contain a record with this country code
	 * and telephone number, add it. There is no delete and there is not a
	 * modify since a country can have lots of phone numbers.
	 * 
	 * @param targetCountry
	 * @param targetTelNum
	 * @return
	 * @throws SQLException
	 * @throws TelephoneNumberException
	 * @throws PropertyNotFoundException
	 * @throws ClassNotFoundException
	 * @throws FileNotFoundException
	 * @throws TableNotInDatabaseException
	 */
	public int updateTelephoneTable(int targetCountry, String targetTelNum)
			throws SQLException, TelephoneNumberException,
			PropertyNotFoundException, ClassNotFoundException,
			FileNotFoundException {
		this.risTelephoneRecords++;
		// making the raw data from RIS into a TelephoneStruct does some
		// processing on the strings. It removes the formating and the extension
		TelephoneStruct tempTelephoneStruct = new TelephoneStruct(
				targetCountry, targetTelNum);

		int targetCountryNumber = tempTelephoneStruct.getCountryNumber();
		String targetPhoneNumber = tempTelephoneStruct.getTelephoneNumber();
		int telephoneRecordKey = NotFound;
		String msg = "TelephoneSynchronizer.updateTelephoneTable() country: "
				+ targetCountry + ", telNum: " + targetTelNum + "  \n";
		try {
			this.isValid(targetCountryNumber, targetPhoneNumber);
			telephoneRecordKey = this.findTelephoneTableRecord(
					targetCountryNumber, targetPhoneNumber);
			if (telephoneRecordKey == NotFound) {
				telephoneRecordKey = this.addTelephoneTableRecord(
						targetCountryNumber, targetPhoneNumber);
				this.griidcRecordsAdded++;
			} else {

				this.griidcDuplicates++;
				throw new TelephoneNumberException(
						"Error in RIS People duplicate Telephone Information -  country: "
								+ targetCountryNumber + ", number: "
								+ targetPhoneNumber);
			}
			return telephoneRecordKey;
		} catch (TelephoneNumberException e) {
			this.risTelephoneErrors++;
			if (TelephoneSynchronizer.isDebug()) {
				System.out.println(msg + e.getMessage());
				e.printStackTrace();
			}
			throw e;
		} catch (SQLException e) {
			this.risTelephoneErrors++;
			if (TelephoneSynchronizer.isDebug()) {
				System.out.println(msg + e.getMessage());
				e.printStackTrace();
			}
			throw new TelephoneNumberException(
					"\nCan't update or add GRIIDC Telephone number - "
							+ e.getMessage());
		} catch (FileNotFoundException e) {
			this.risTelephoneErrors++;
			if (TelephoneSynchronizer.isDebug()) {
				System.out.println(msg + e.getMessage());
				e.printStackTrace();
			}
			throw e;
		} catch (ClassNotFoundException e) {
			this.risTelephoneErrors++;
			if (TelephoneSynchronizer.isDebug()) {
				System.out.println(msg + e.getMessage());
				e.printStackTrace();
			}
			throw e;
		} catch (PropertyNotFoundException e) {
			this.risTelephoneErrors++;
			if (TelephoneSynchronizer.isDebug()) {
				System.out.println(msg + e.getMessage());
				e.printStackTrace();
			}
			throw e;
		}
	}

	private boolean isValid(int targetCountry, String targetTelNum)
			throws TelephoneNumberException {
		if (!MiscUtils.doesCountryExist(targetCountry)) {
			msg = "Telephone number referred to a non existant country code: "
					+ targetCountry;
			throw new TelephoneNumberException(msg);
		}
		MiscUtils.isValidPhoneNumber(targetTelNum);
		return true;
	}

	/**
	 * add a new Telephone record. Return the key if successful. Throws
	 * 
	 * @param targetCountryNumber
	 * @param targetPhoneNumber
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws TelephoneNumberException
	 */
	private int addTelephoneTableRecord(int targetCountryNumber,
			String targetPhoneNumber) throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		int rtn = -1;
		DbColumnInfo info[] = getInsertClauseInfo(targetCountryNumber,
				targetPhoneNumber);
		try {
			String query = RdbmsUtils.formatInsertStatement(TableName, info);
			RdbmsUtils.getGriidcDbConnectionInstance().executeQueryBoolean(
					query);

			rtn = this.findTelephoneTableRecord(targetCountryNumber,
					targetPhoneNumber);
		} catch (FileNotFoundException e) {
			throw new FileNotFoundException("GRIIDC Telephone: "
					+ e.getMessage());
		} catch (SQLException e) {
			throw new SQLException("GRIIDC Telephone: " + e.getMessage());
		} catch (ClassNotFoundException e) {
			throw new ClassNotFoundException("GRIIDC Telephone: "
					+ e.getMessage());
		} catch (PropertyNotFoundException e) {
			throw new PropertyNotFoundException("GRIIDC Telephone: "
					+ e.getMessage());
		}
		return rtn;
	}

	/**
	 * using the TelephoneStruct temporary, find the phone number record and
	 * return it as a new TelephoneStruct. Return null if nothing found that
	 * matches.
	 * 
	 * @return
	 * @throws SQLException
	 * @throws FileNotFoundException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws TableNotInDatabaseException
	 */
	private int findTelephoneTableRecord(int targetCountryNumber,
			String targetPhoneNumber) throws SQLException,
			FileNotFoundException, ClassNotFoundException,
			PropertyNotFoundException {

		String phoneNum = null;
		int countryNum = -1;
		int telephoneKey = NotFound;
		DbColumnInfo[] wc = this.getWhereClauseInfo(targetCountryNumber,
				targetPhoneNumber);
		String selectQuery = RdbmsUtils.formatSelectStatement(TableName, wc);
		ResultSet rs = RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryResultSet(selectQuery);
		while (rs.next()) {
			telephoneKey = rs.getInt(TelephoneKeyColName);
			phoneNum = rs.getString(TelephoneNumberColName);
			countryNum = rs.getInt(CountryNumberColName);
			if (targetPhoneNumber.equals(phoneNum.trim())
					&& targetCountryNumber == countryNum) {
				if (TelephoneSynchronizer.isDebug()) {
					System.out.println("Found matching key: " + telephoneKey
							+ ", country: " + countryNum + ", phone number: "
							+ phoneNum);
				}
				return telephoneKey;
			}
		}
		return NotFound;
	}

	private DbColumnInfo[] getInsertClauseInfo(int targetCountryNumber,
			String targetPhoneNumber) throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		DbColumnInfo dci1 = tci.getDbColumnInfo(CountryNumberColName);
		dci1.setColValue(String.valueOf(targetCountryNumber));

		DbColumnInfo dci2 = tci.getDbColumnInfo(TelephoneNumberColName);
		dci2.setColValue(String.valueOf(targetPhoneNumber));

		DbColumnInfo[] dcia = { dci1, dci2 };
		return dcia;
	}

	private DbColumnInfo[] getWhereClauseInfo(int targetCountryNumber,
			String targetPhoneNumber) throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		DbColumnInfo d1 = tci.getDbColumnInfo(CountryNumberColName);
		d1.setColValue(String.valueOf(targetCountryNumber));

		DbColumnInfo d2 = tci.getDbColumnInfo(TelephoneNumberColName);
		d2.setColValue(String.valueOf(targetPhoneNumber));

		DbColumnInfo[] info = { d1, d2 };
		return info;
	}

	public static int getNotfound() {
		return NotFound;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean deBug) {
		Debug = deBug;
	}

	public int getGriidcRecordsAdded() {
		return griidcRecordsAdded;
	}

	public int getGriidcDuplicates() {
		return griidcDuplicates;
	}

	public int getRisTelephoneRecords() {
		return risTelephoneRecords;
	}

	public int getRisTelephoneErrors() {
		return risTelephoneErrors;
	}
}
