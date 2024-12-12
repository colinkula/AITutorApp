$(document).ready(function() {
    // Reset button functionality
    $('#resetButton').on('click', function() {
        // Clear all form fields
        $('#logForm')[0].reset();

        // Clear the token total
        $('#tokenTotal').empty();

        // Clear the log output
        $('#logOutput').empty();
    });

    // Function to gather the values of the form fields
    function getFormValues() {
        const time = $("#logTime").val();
        const date = $("#logDate").val();
        const minCost = $("#minCost").val();
        const maxCost = $("#maxCost").val();
        const prompt = $("#promptField").val();

        return { time, date, minCost, maxCost, prompt };
    }

    // Function to validate the date and time format if they are entered
    function validateDateTime(date, time) {
        // Regex patterns for date and time validation
        const datePattern = /^\d{4}-\d{2}-\d{2}$/;  // yyyy-mm-dd
        const timePattern = /^\d{2}:\d{2}:\d{2}$/;  // hh:mm:ss

        // Validate date and time format only if they are not empty
        if (date && !datePattern.test(date)) {
            alert('Invalid date format. Please use yyyy-mm-dd.');
            return false;
        }
        if (time && !timePattern.test(time)) {
            alert('Invalid time format. Please use hh:mm:ss.');
            return false;
        }
        return true;
    }

    // Submit button functionality
    $('#logForm').on('submit', function(event) {
      event.preventDefault();  // Prevent form submission to keep the page from refreshing
      const formValues = getFormValues();

      // Validate date and time formats before submitting, only if they are provided
      if (!validateDateTime(formValues.date, formValues.time)) {
        return;  // Stop submission if validation fails
      }

      // Proceed with your form data if everything is valid
      console.log("Form Values for Search:", formValues);
      
      $.ajax({
        url: 'http://172.17.12.57/final.php/getLog',
        method: 'GET',
        data: {
          time: formValues.time || 'NA',  // Use empty string if time is not provided
          date: formValues.date || 'NA',  
          mincost: formValues.minCost || 'NA', 
          maxcost: formValues.maxCost || 'NA',  
          prompt: formValues.prompt || 'NA',  
          numrecords: 99999  
        }
      })
      .done(function(logData) {
        if (logData.result && logData.result.length > 0) {
          const logContainer = $("<div>").addClass("row");
          let totalTokens = 0;

          logData.result.forEach(log => {
            // Left Column (LogID, Timestamp, Token Cost, User Prompt)
            const leftCol = $("<div>")
              .addClass("col-md-6 border-end pe-3")
              .append(
                $("<div>")
                  .addClass("section mb-3")
                  .append($("<h5>").addClass("fw-bold text-primary").text("Log ID"))
                  .append($("<p>").addClass("text-muted").text(log.logID))
              )
              .append(
                $("<div>")
                  .addClass("section mb-3")
                  .append($("<h5>").addClass("fw-bold text-primary").text("Timestamp"))
                  .append($("<p>").addClass("text-muted").text(log.timeStamp))
              )
              .append(
                $("<div>")
                  .addClass("section mb-3")
                  .append($("<h5>").addClass("fw-bold text-primary").text("Token Cost"))
                  .append($("<p>").addClass("text-muted").text(log.cost))
              )
              .append(
                $("<div>")
                  .addClass("section")
                  .append($("<h5>").addClass("fw-bold text-primary").text("User Prompt"))
                  .append($("<p>").addClass("text-muted").text(log.inputData))
              );

            // Right Column (HTML Output)
            const rightCol = $("<div>")
              .addClass("col-md-6 border p-3")
              .append($("<div>").html(log.outputData));

            // Append both columns to the row
            logContainer.append(leftCol).append(rightCol);

            // Add the token cost to the total
            totalTokens += parseFloat(log.cost) || 0;
          });

          // Display the token total
          $("#tokenTotal").empty().append(totalTokens);

          // Display the log container
          $("#logOutput").empty().append(logContainer);

          // Show the total tokens for the selected date
          $("#tokenTotal").show();  // Show the total tokens section

        } else {
          $("#logOutput").text("No logs found.");
          $("#tokenTotal").text("No tokens found.");
        }
        console.log("log request successful:", logData);
      })
      .fail(function(error) {
        console.log("Error getting log:", error);
      });
    });
  });
