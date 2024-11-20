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
	public static function addLog ($inputdata, $outputdata) {
		try {

			EXEC_SQL("INSERT INTO log (inputData, outputData) VALUES (?, ?)", $inputdata, $outputdata);
			
			$retData["status"] = 0;
			$retData["message"] = "Log entry added successfully";
		} catch(Exception $e) {
			$retData["status"] = 1;
			$retData["message"] = $e->getMessage();
		}
		return json_encode ($retData);
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
	public static function getLog($date, $numrecords) {
	    $retData = array();
	    try {
	        $numRecords = intval($numrecords);

		$query = "SELECT timeStamp, inputData, outputData 
                 	 FROM log 
                 	 WHERE date(timeStamp) = ?
                         ORDER BY timeStamp DESC
                	 LIMIT ?";
        
        	$retData["result"] = GET_SQL($query, $date, $numRecords);

        	$retData["status"] = 0;
        	$retData["message"] = "";
	    } catch (Exception $e) {
	        $retData["status"] = 1;
	        $retData["message"] = $e->getMessage();
	    }

	    return json_encode($retData);
	}

}

