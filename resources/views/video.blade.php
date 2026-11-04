<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Frame extractor</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --paper: #e8edf1;
            --surface: #f8fafb;
            --ink: #16212b;
            --muted: #566673;
            --line: #c3cdd6;
            --accent: #0b6b63;
            --accent-hover: #085750;
            --error: #a8231b;
            --error-bg: #fbeceb;
            --display: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
            --body: "Figtree", "Segoe UI", system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--paper);
            color: var(--ink);
            font-family: var(--body);
            font-size: 1rem;
            line-height: 1.5;
        }

        .page {
            max-width: 1080px;
            margin: 0 auto;
            padding: 3rem 1.5rem 4rem;
        }

        header { margin-bottom: 2.5rem; }

        h1 {
            margin: 0 0 0.5rem;
            font-family: var(--display);
            font-weight: 700;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.05;
            letter-spacing: -0.02em;
        }

        header p {
            margin: 0;
            max-width: 52ch;
            color: var(--muted);
        }

        .workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 2.5rem;
            align-items: start;
        }

        h2 {
            margin: 0 0 0.75rem;
            font-family: var(--display);
            font-weight: 600;
            font-size: 1.125rem;
            letter-spacing: -0.01em;
        }

        /* Source video */
        video {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #0d141a;
            border-radius: 6px;
        }

        /* Form */
        form { margin-top: 1.5rem; }

        label {
            display: block;
            margin-bottom: 0.375rem;
            font-weight: 600;
        }

        .hint {
            margin: 0 0 0.75rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .controls {
            display: flex;
            gap: 0.5rem;
        }

        input[type="number"] {
            flex: 1;
            min-width: 0;
            padding: 0.7rem 0.85rem;
            font: inherit;
            font-variant-numeric: tabular-nums;
            color: var(--ink);
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 6px;
        }

        input[type="number"]::placeholder { color: #8b99a5; }

        button,
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.25rem;
            font: inherit;
            font-weight: 600;
            color: #fff;
            background: var(--accent);
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        button:hover,
        .button:hover { background: var(--accent-hover); }

        button:disabled {
            opacity: 0.6;
            cursor: progress;
        }

        input:focus-visible,
        button:focus-visible,
        .button:focus-visible {
            outline: 3px solid #5aa7a0;
            outline-offset: 2px;
        }

        .message {
            margin: 0.875rem 0 0;
            padding: 0.7rem 0.85rem;
            color: var(--error);
            background: var(--error-bg);
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .message[hidden] { display: none; }

        /* Result panel */
        .frame {
            position: relative;
            aspect-ratio: 16 / 9;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 2px dashed var(--line);
            border-radius: 6px;
            color: var(--muted);
            text-align: center;
            padding: 1rem;
        }

        .frame.filled {
            border: 0;
            background: #0d141a;
            padding: 0;
        }

        .frame img {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .frame.filled img { display: block; }
        .frame.filled .empty { display: none; }

        .counter {
            display: none;
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            padding: 0.2rem 0.65rem;
            font-family: var(--display);
            font-weight: 700;
            font-size: 1.5rem;
            line-height: 1.3;
            font-variant-numeric: tabular-nums;
            color: #fff;
            background: rgba(13, 20, 26, 0.78);
            border-radius: 4px;
        }

        .frame.filled .counter { display: block; }

        .result-actions {
            margin-top: 1rem;
            min-height: 2.75rem;
        }

        .result-actions[hidden] { display: none; }

        @media (max-width: 800px) {
            .workspace { grid-template-columns: 1fr; gap: 2rem; }
            .page { padding-top: 2rem; }
        }
    </style>
</head>

<body>
    <main class="page">
        <header>
            <h1>Frame extractor</h1>
            <p>Pick a frame number from the video and save it as a PNG image.</p>
        </header>

        <div class="workspace">
            <section aria-labelledby="source-title">
                <h2 id="source-title">Source video</h2>

                <video controls preload="metadata">
                    <source src="{{ asset('storage/assets/example.mp4') }}" type="video/mp4">
                    Your browser does not support video playback.
                </video>

                <form id="frame-form" novalidate>
                    <label for="frame">Frame number</label>
                    <p class="hint" id="frame-hint">Whole number, starting at 0 for the first frame.</p>

                    <div class="controls">
                        <input
                            type="number"
                            id="frame"
                            name="frame"
                            min="0"
                            step="1"
                            inputmode="numeric"
                            placeholder="For example, 120"
                            aria-describedby="frame-hint message"
                        >
                        <button type="submit" id="submit">Extract frame</button>
                    </div>

                    <p class="message" id="message" role="alert" hidden></p>
                </form>
            </section>

            <section aria-labelledby="result-title" aria-live="polite">
                <h2 id="result-title">Extracted frame</h2>

                <div class="frame" id="frame-box">
                    <p class="empty">Your frame will appear here.</p>
                    <img id="preview" alt="">
                    <span class="counter" id="counter"></span>
                </div>

                <div class="result-actions" id="result-actions" hidden>
                    <a class="button" id="save" href="#" download>Save PNG</a>
                </div>
            </section>
        </div>
    </main>

    <script>
        const form = document.getElementById('frame-form');
        const input = document.getElementById('frame');
        const submit = document.getElementById('submit');
        const message = document.getElementById('message');
        const frameBox = document.getElementById('frame-box');
        const preview = document.getElementById('preview');
        const counter = document.getElementById('counter');
        const actions = document.getElementById('result-actions');
        const save = document.getElementById('save');

        let currentUrl = null;

        function showError(text) {
            message.textContent = text;
            message.hidden = false;
        }

        function clearError() {
            message.textContent = '';
            message.hidden = true;
        }

        function setLoading(loading) {
            submit.disabled = loading;
            submit.textContent = loading ? 'Extracting...' : 'Extract frame';
        }

        function showResult(blob, frame) {
            if (currentUrl) URL.revokeObjectURL(currentUrl);
            currentUrl = URL.createObjectURL(blob);

            preview.src = currentUrl;
            preview.alt = `Video frame number ${frame}`;
            counter.textContent = String(frame).padStart(4, '0');
            frameBox.classList.add('filled');

            save.href = currentUrl;
            save.download = `frame-${frame}.png`;
            actions.hidden = false;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearError();

            const raw = input.value.trim();
            const frame = Number(raw);

            if (raw === '' || !Number.isInteger(frame) || frame < 0) {
                showError('Enter a whole number, 0 or higher.');
                input.focus();
                return;
            }

            setLoading(true);

            try {
                const response = await fetch(`/api/video/download/${frame}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => null);

                    if (data && data.total_frames) {
                        const lastFrame = data.total_frames - 1;
                        input.max = lastFrame;
                        showError(`This video has frames 0 to ${lastFrame}. Enter a number in that range.`);
                    } else {
                        showError((data && data.error) || 'Could not extract the frame. Try again.');
                    }
                    return;
                }

                showResult(await response.blob(), frame);
            } catch (error) {
                showError('Could not reach the server. Check your connection and try again.');
            } finally {
                setLoading(false);
            }
        });
    </script>
</body>

</html>