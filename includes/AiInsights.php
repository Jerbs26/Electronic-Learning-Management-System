<?php


require_once __DIR__ . '/ai_guard.php';

final class AiInsights
{

    public static function suggestActionPlan(
        string $apiKey,
        string $studentName,
        string $courseName,
        float $average,
        array $weakItems
    ): array {
        if ($apiKey === '') {
            return ['error' => 'AI insights are not available right now. Please contact the admin.'];
        }

        $weakList = $weakItems
            ? implode("\n", array_map(fn($w) => "- {$w['label']}: {$w['percent']}%", $weakItems))
            : 'No specific low-scoring items — overall average is simply below the passing threshold.';

        $systemPrompt =
            "You are an instructional support assistant helping a Philippine Senior High School / High School " .
            "teacher decide how to help a struggling student. Given the student's overall average and their " .
            "weakest graded items in one subject, suggest a short, concrete action plan the teacher can " .
            "realistically carry out. Keep it to 3-4 sentences: name the likely gap, one concrete classroom " .
            "action (e.g. targeted review, peer tutoring, a short diagnostic check, remedial activity), and one " .
            "follow-up step (e.g. re-quiz in a week, parent/guardian conference if no improvement). " .
            "Be specific and practical, not generic encouragement. " .
            "Respond with plain text only — no markdown, no headers, no bullet points, no JSON.";

        $userPrompt =
            "Student: {$studentName}\n" .
            "Subject: {$courseName}\n" .
            "Overall average: {$average}%\n\n" .
            "Weakest graded items:\n{$weakList}";

        $payload = json_encode([
            'model'            => 'openai/gpt-oss-120b',
            'messages'         => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens'       => 1024,
            'temperature'      => 0.4,
            'reasoning_effort' => 'low',
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        // Raw AI-service errors go to the log only (includes/ai_guard.php)
        if ($curlErr || !$response || $httpCode !== 200) {
            return ['error' => aiFriendlyError((int) $httpCode, $response === false ? null : $response, (string) $curlErr, 'The AI helper')];
        }

        $data = json_decode($response, true);
        $content = is_array($data) ? trim((string) ($data['choices'][0]['message']['content'] ?? '')) : '';
        if ($content === '') {
            return ['error' => 'Empty response from AI. Please try again.'];
        }
        // Strip any stray markdown the model might add anyway.
        $content = preg_replace('/^\s*[#*\-]+\s*/m', '', $content);
        $content = str_replace(['**', '__'], '', $content);

        return ['plan' => trim($content)];
    }
}
