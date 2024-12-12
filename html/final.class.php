<?php 
class final_rest
{



/**
 * @api  /api/v1/setTemp/
 * @apiName setTemp
 * @apiDescription Add remote temperature measurement
 *
 * @apiParam {string} location
 * @apiParam {String} sensor
 * @apiParam {double} value
 *
 * @apiSuccess {Integer} status
 * @apiSuccess {string} message
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":0,
 *              "message": ""
 *     }
 *
 * @apiError Invalid data types
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":1,
 *              "message":"Error Message"
 *     }
 *
 */
	public static function setTemp ($location, $sensor, $value)

	{
		if (!is_numeric($value)) {
			$retData["status"]=1;
			$retData["message"]="'$value' is not numeric";
		}
		else {
			try {
				EXEC_SQL("insert into temperature (location, sensor, value, date) values (?,?,?,CURRENT_TIMESTAMP)",$location, $sensor, $value);
				$retData["status"]=0;
				$retData["message"]="insert of '$value' for location: '$location' and sensor '$sensor' accepted";
			}
			catch  (Exception $e) {
				$retData["status"]=1;
				$retData["message"]=$e->getMessage();
			}
		}

		return json_encode ($retData);
	}


/**
 * @api  /api/v1/getLevel/
 * @apiName getLevel
 * @apiDescription Return all level data from database
 *
 * @apiSuccess {Integer} status
 * @apiSuccess {string} message
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":0,
 *              "message": ""
 *              "result": [
 *                { 
 *                  levelID: 1,
 *                  description: "",
 *                  prompt: ""
 *              ]
 *     }
 *
 * @apiError Invalid data types
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":1,
 *              "message":"Error Message"
 *     }
 *
 */
  public static function getLevel () {
		try {
                        $retData["status"]=0;
                        $retData["message"]="";
			$retData["result"]=GET_SQL("select * from level order by sortCode");
		}
                catch  (Exception $e) {
                        $retData["status"]=1;
                        $retData["message"]=$e->getMessage();
		}
		return json_encode ($retData);
  }

/**
 * @api  /api/v1/addLog/
 * @apiName addLog
 * @apiDescription Add record to logfile
 *
 * @apiParam {string} level
 * @apiParam {String} systemPrompt
 * @apiParam {String} userPrompt
 * @apiParam {string} chatResponse
 * @apiParam {Integer} inputTokens
 * @apiParam {Integer} outputTokens
 *
 * @apiSuccess {Integer} status
 * @apiSuccess {string} message
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":0,
 *              "message": ""
 *     }
 *
 * @apiError Invalid data types
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":1,
 *              "message":"Error Message"
 *     }
 *
 *
 */
public static function addLog($inputdata, $outputdata, $cost) {
    try {
        // Set the desired time zone
        $timezone = new DateTimeZone('America/New_York'); 
        $datetime = new DateTime('now', $timezone); 
        $timeStamp = $datetime->format('Y-m-d H:i:s'); // Format for SQL

        // Insert the log entry with the specified timestamp
        EXEC_SQL("INSERT INTO log (inputData, outputData, timeStamp, cost) VALUES (?, ?, ?, ?)", $inputdata, $outputdata, $timeStamp, $cost);
        
        // Prepare the response data
        $retData["status"] = 0;
        $retData["message"] = "Log entry added successfully";
    } catch(Exception $e) {
        $retData["status"] = 1;
        $retData["message"] = $e->getMessage();
    }
    
    return json_encode($retData);
}
  

/**
 * @api  /api/v1/getLog/
 * @apiName getLog
 * @apiDescription Retrieve Log Records
 *
 * @apiParam {string} date
 * @apiParam {String} numRecords
 *
 * @apiSuccess {Integer} status
 * @apiSuccess {string} message
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":0,
 *              "message": ""
 *              "result": [
 *                { 
 *                  timeStamp: "YYYY-MM-DD HH:MM:SS",
 *                  level: "",
 *                  systemPrompt: "",
 *                  userPrompt: "",
 *                  chatResponse: "",
 *                  inputTokens: 0,
 *                  outputTokens: 0
 *              ]
 *     }
 *
 * @apiError Invalid data types
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 200 OK
 *     {
 *              "status":1,
 *              "message":"Error Message"
 *     }
 *
 */

 // http://172.17.12.57/final.php/getLog?time=NA&date=NA&mincost=NA&maxcost=NA&prompt=NA&numrecords=9999
 public static function getLog($time, $date, $mincost, $maxcost, $prompt, $numrecords) {
    $retData = array();
    try {
        // Initialize the base query and parameters array
        $query = "SELECT logId, timeStamp, inputData, outputData, cost FROM log WHERE 1=1";
        $params = [];

        // Base value for empty fields
        $emptyValue = "NA"; 

        // Check for date and time, skip if the base value is used
        if ($date !== $emptyValue) {
            $query .= " AND DATE(timeStamp) = ?";
            $params[] = $date;
        }
        if ($time !== $emptyValue) {
            $query .= " AND TIME(timeStamp) = ?";
            $params[] = $time;
        }

        // Check for valid mincost/maxcost, skip if "NA"
        if ($mincost !== $emptyValue) {
            $query .= " AND cost >= ?";
            $params[] = intval($mincost);  // Ensure mincost is an integer
        }
        if ($maxcost !== $emptyValue) {
            $query .= " AND cost <= ?";
            $params[] = intval($maxcost);  // Ensure maxcost is an integer
        }

        // Check if prompt is valid
        if ($prompt !== $emptyValue) {
            $query .= " AND inputData LIKE ?";
            $params[] = '%' . $prompt . '%';
        }

        // Add ordering and limit
        $query .= " ORDER BY timeStamp DESC";
        if (!empty($numrecords)) {
            $query .= " LIMIT ?";
            $params[] = intval($numrecords);
        }

        // Execute the query with parameters
        $retData["result"] = GET_SQL($query, ...$params);
        $retData["status"] = 0;
        $retData["message"] = "";
    } catch (Exception $e) {
        $retData["status"] = 1;
        $retData["message"] = $e->getMessage();
    }

    return json_encode($retData);
}

}

