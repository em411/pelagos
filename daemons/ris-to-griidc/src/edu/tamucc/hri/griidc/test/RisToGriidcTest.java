package edu.tamucc.hri.griidc.test;

import java.io.FileNotFoundException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.RisPropertiesAccess;
import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.GriidcExceptionService;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

/**
 * A consolidation of test routines
 * 
 * @author jvh
 * 
 */
public class RisToGriidcTest {
	public static RisPropertiesAccess propsAccess = null;
	public static String msg = null;

	public static boolean testApostropheEscape() {
		System.out.println("--- testApostropheEscape ---");
		String[] ses = { "O\'Connle", "O\'aaa\'bbb\'ccc\'ddd\'",
				"Joe V. Holland" };
		for (String s : ses) {
			String before = s;
			String after = RdbmsConnection.escapeApostrophe(before);
			System.out.println("Before: " + before + " -> after: " + after);
		}
		return true;
	}

	public static boolean testRisDbConnection() {
		System.out.println("--- testRisDbConnection ---");

		// connect to the databases
		String msg = "SyncGriidcToRis - Could not connect to RIS database ";
		RdbmsConnection con = null;
		try {
			con = RdbmsUtils.getRisDbConnectionInstance();
		} catch (FileNotFoundException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (SQLException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (ClassNotFoundException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println("RIS connection\n" + con.toString());
		return true;
	}

	public static boolean testGriidcDbConnection() {
		System.out.println("--- testGriidcDbConnection ---");
		RdbmsConnection con = null;
		String msg = "SyncGriidcToRis - Could not connect to GRIIDC database ";
		try {
			con = RdbmsUtils.getGriidcDbConnectionInstance();
		} catch (FileNotFoundException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (SQLException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (ClassNotFoundException e) {
			GriidcExceptionService.fatalException(e, msg);
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println("GRIIDC connection\n" + con.toString());
		return true;
	}

	public static boolean testGetGriidcTables(boolean tableNamesOnly) throws TableNotInDatabaseException {
		System.out
				.println("\n\n-------------- testGetGriidcTables ------------------");
		System.out.println("GRIIDC tables and their columns");
		RdbmsConnection con = null;
		String[] tableNames = null;
		String[] colNames = null;
		try {
			con = RdbmsUtils.getGriidcDbConnectionInstance();
			tableNames = con.getTableNamesForDatabase();
			for (String t : tableNames) {
				if (tableNamesOnly) {
					System.out.println("\t" + t);
				} else {
					colNames = con.getColumnNamesFromTable(t);
					tableReport(t, colNames);
				}
			}
		} catch (FileNotFoundException e1) {
			e1.printStackTrace();
		} catch (SQLException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return true;
	}

	private static void tableReport(String t, String[] colNames) {
		System.out.println("\nTable: " + t + " column names are: ");
		for (String n : colNames) {
			System.out.print("\t" + n + "\n");
		}
	}

	public static boolean testGetRisTables(boolean tableNamesOnly) throws TableNotInDatabaseException {
		System.out
				.println("\n\n------------- testGetRisTables --------------------");
		System.out.println("RIS tables and their columns");
		RdbmsConnection con = null;
		String[] tableNames = null;
		String[] colNames = null;
		try {
			con = RdbmsUtils.getRisDbConnectionInstance();
			tableNames = con.getTableNamesForDatabase();
			for (String t : tableNames) {
				if (tableNamesOnly) {
					System.out.println("\t" + t);
				} else {
					colNames = con.getColumnNamesFromTable(t);
					tableReport(t, colNames);
				}
			}
		} catch (FileNotFoundException e1) {
			e1.printStackTrace();
		} catch (SQLException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return true;
	}
	public static void testFindGriidcPostalAreaNumber() {
		String[][] zzz = {
				{"United States","Oklahoma","Eakly","73033"},
				{"United States","Florida","Palm Beach Gardens","33410"},
				{"France","Pays de la Loire","Pouzauges","85702 CEDEX"},
				{"Spain","Castilla - Leon","Velilla","47114"},	
				{"Germany","Rheinland-Pfalz","Carlsberg","67316"},
		};
		
		
	}

	public static void testCountryNameToCountryCode() {
		String[] countryNames = { "Andorra", "United Arab Emirates",
				"Afghanistan", "Antigua and Barbuda", "Anguilla", "Albania",
				"Armenia", "Angola", "Antarctica", "Argentina",
				"American Samoa", "Austria", "Australia", "Aruba",
				"Aland Islands", "Azerbaijan", "Bosnia and Herzegovina",
				"Barbados", "Bangladesh", "Belgium", "Burkina Faso",
				"Bulgaria", "Bahrain", "Burundi", "Benin", "Saint Barthelemy",
				"Bermuda", "Brunei", "Bolivia",
				"Bonaire, Saint Eustatius and Saba ", "Brazil", "Bahamas",
				"Bhutan", "Bouvet Island", "Botswana", "Belarus", "Belize",
				"Canada", "Cocos Islands", "Democratic Republic of the Congo",
				"Central African Republic", "Republic of the Congo",
				"Switzerland", "Ivory Coast", "Cook Islands", "Chad",
				"French Southern Territories", "Togo", "Thailand",
				"Tajikistan", "Tokelau", "East Timor", "Turkmenistan",
				"Tunisia", "Tonga", "Turkey", "Trinidad and Tobago", "Tuvalu",
				"Taiwan", "Tanzania", "Ukraine", "Uganda",
				"United States Minor Outlying Islands", "United States",
				"Uruguay", "Uzbekistan", "Vatican",
				"Saint Vincent and the Grenadines", "Venezuela",
				"British Virgin Islands", "U.S. Virgin Islands", "Vietnam",
				"Vanuatu", "Wallis and Futuna", "Samoa", "Yemen", "Mayotte",
				"South Africa", "Zambia", "Zimbabwe", "Serbia and Montenegro",
				"Netherlands Antilles" };
	
		
		
	}

	public static void main(String[] args) {

		// RisToGriidcTest.testApostropheEscape();

		// RisToGriidcTest.testPropertiesAccess();

		// RisToGriidcTest.getRisPropertiesAccess();

		// RisToGriidcTest.testRisDbConnection();

		// RisToGriidcTest.testGriidcDbConnection();

		//RisToGriidcTest.testGetGriidcTables(true);

		//RisToGriidcTest.testGetRisTables(true);
		
		// RisToGriidcTest.testCountryNameToCountryCode();
		RisToGriidcTest.testFindGriidcPostalAreaNumber();

	}

}
