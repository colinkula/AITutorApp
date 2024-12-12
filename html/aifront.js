// Function to handle form submission
function submitForm() {
    console.log("Form submission started.");
    
    var level = $("#level").val();  // Get selected level (as a string)
    var userPrompt = $("#userPrompt").val();  // Get user input
    var systemPrompt = "";  // Initialize queryPrompt

    // Validate the inputs
    if (!level) {
        console.log("Level is missing.");
        alert("Please select a level.");
        return;
    }
    if (!userPrompt) {
        console.log("User prompt is missing.");
        alert("Please enter your question.");
        return;
    }

    // Convert level to an integer for comparison
    level = parseInt(level, 10);
    console.log("Converted level: " + level);

    // Fetch the selected level's prompt
    fetchLevelPrompt(level, userPrompt);
}

// Function to fetch the selected level's prompt
function fetchLevelPrompt(level, userPrompt) {
    console.log("Fetching level prompt for level: " + level);
    displayError("Currently constructing response...");

    $.ajax({
        url: 'http://172.17.12.57/final.php/getLevel',
        method: "GET",
        contentType: "application/json"
    })
    .done(function(levelData) {
        console.log("Level data received:", levelData);

        if (levelData.status === 0 && levelData.result) {
            var systemPrompt = getSystemPrompt(levelData.result, level);
            console.log("System prompt found:", systemPrompt);

            // Encode prompts for URL
            userPrompt = encodeURIComponent(userPrompt.replace(/ /g, '+'));
            systemPrompt = encodeURIComponent(systemPrompt.replace(/ /g, '+'));

            // Fetch chat response with encoded prompts
            fetchChatResponse(userPrompt, systemPrompt);
        } else {
            console.log("Error fetching levels data.");
        }
    })
    .fail(function(error) {
        console.log("Error fetching levels:", error);
    });
}

// Function to get the system prompt for the selected level
function getSystemPrompt(levelData, level) {
    console.log("Searching for prompt for level:", level);
    var systemPrompt = "";
    for (let i = 0; i < levelData.length; i++) {
        if (level === levelData[i].levelid) {
            systemPrompt = levelData[i].prompt;
            break;
        }
    }
    return systemPrompt;
}

// Function to fetch the chat response based on user and system prompts
function fetchChatResponse(userPrompt, systemPrompt) {
    console.log("Fetching chat response with userPrompt:", userPrompt, "and systemPrompt:", systemPrompt);

    $.ajax({
        url: 'https://ceclnx01.cec.miamioh.edu/~johnsok9/cse383/final/index.php/chatgpt',
        method: "GET",
        data: {
            user_prompt: userPrompt,
            system_prompt: systemPrompt,
            uniqueid: "kulacs",
            auth: "Aim9oozo"
        }
    })
    .done(function(chatData) {
        console.log("Chat response received:", chatData);
        var content = chatData.result.message.choices[0].message.content;
        var totalTokens = chatData.result.message.usage.total_tokens;

        console.log("Formatted content:", content);
        console.log("Total tokens:", totalTokens);

        $("#chatResponse").html(content).show();
        displayError("Tokens: " + totalTokens);

        userInput = $("#userPrompt").val();
        userinput = userInput.replace(/ /g, '+');
        
        // Log the chat request
        logChatRequest(userInput, chatData);
    })
    .fail(function(error) {
        console.log("Error fetching chat response:", error);
    });
}

// Function to log the chat request to the server
function logChatRequest(userPrompt, chatData) {
    console.log("Logging chat request:", userPrompt);
    var content = chatData.result.message.choices[0].message.content;
    var totalTokens = chatData.result.message.usage.total_tokens;

    // Input data is the user prompt
    // Output data is the content of the chat response
    // Cost is the total_tokens of the chaty response
    $.ajax({
        url: 'http://172.17.12.57/final.php/addLog',
        method: 'POST',
        data: {
            inputData: userPrompt,
            outputData: content,
            cost: totalTokens
        }
    })
    .done(function(logData) {
        $("#p1").text("worked: ");
        console.log("Chat log request successful:", logData);
    })
    .fail(function(error) {
        $("#p1").text("failed: ");
        console.log("Error logging chat request:", error);
    });
}

// Function to display error messages
function displayError(message) {
    console.log("Displaying error:", message);
    $("#errorMessage").text(message).show();
}
    
// Initialize the form on page load
$(document).ready(function() {
    // Bind the submit function to the form
    $("#submitBtn").click(function(event) {
        event.preventDefault();  // Prevent form submission
        submitForm();
    });
});
