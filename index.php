<?php
session_start();
require_once 'config.php'; // Separate config for security and sanity

// Database connection with error handling because we’re pros
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Oops! Our database took a nap. Try again later.");
}

// Secure API key storage (no hardcoding, folks!)
$api_key = getenv("XAI_API_KEY") ?: XAI_API_KEY;
if (!$api_key) {
    error_log("No xAI API key found. Check your environment or config.php!");
    die("API key missing. I’m not psychic!");
}

function generate_faq($questions, $api_key) {
    /* Generate FAQ from questions. Trust us, it’s like magic, but with code. */
    $prompt = "You are a concise FAQ writer. Generate clear, professional FAQ entries based on this data:\n" . implode("\n", $questions);
    $ch = curl_init("https://api.x.ai/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $api_key",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            "model" => "grok-3",
            "messages" => [["role" => "user", "content" => $prompt]],
            "max_tokens" => 500,
            "temperature" => 0.5 // Keep it professional, not poetic
        ]),
        CURLOPT_TIMEOUT => 10 // Don’t let it hang forever
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL error: " . curl_error($ch));
        curl_close($ch);
        return "Error: The AI is sulking. Try again later.";
    }
    curl_close($ch);
    $data = json_decode($response, true);
    return $data["choices"][0]["message"]["content"] ?? "Error: No response from AI.";
}

// Handle form submission with security in mind
$faq = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["question"])) {
    $question = filter_input(INPUT_POST, "question", FILTER_SANITIZE_STRING);
    if (strlen($question) > 500) {
        $faq = "Error: Keep it short, Shakespeare!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO questions (text, created_at) VALUES (?, NOW())");
            $stmt->execute([$question]);
            $questions = $db->query("SELECT text FROM questions ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
            $faq = generate_faq($questions, $api_key);
            $_SESSION["faq"] = $faq; // Store for display
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $faq = "Error: Database hiccup. Try again!";
        }
    }
} elseif (isset($_SESSION["faq"])) {
    $faq = $_SESSION["faq"]; // Reuse last FAQ if no new submission
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart FAQ Generator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        #faq { white-space: pre-wrap; margin-top: 20px; padding: 10px; border: 1px solid #ccc; }
        input[type="text"] { width: 70%; padding: 8px; }
        button { padding: 8px 16px; }
    </style>
</head>
<body>
    <h1>Smart FAQ Generator</h1>
    <p>Ask away, and watch the magic happen!</p>
    <form method="POST" action="">
        <input type="text" name="question" placeholder="Ask a question (max 500 chars)" maxlength="500" required>
        <button type="submit">Submit</button>
    </form>
    <div id="faq"><?php echo htmlspecialchars($faq ?: "No FAQs yet. Start asking!"); ?></div>

    <script>
        // Basic client-side validation because we’re thorough
        document.querySelector('form').addEventListener('submit', function(e) {
            const question = document.querySelector('input[name="question"]').value;
            if (question.length < 5) {
                e.preventDefault();
                alert("Please ask something more substantial than 'Hi'.");
            }
        });
    </script>
</body>
</html>
