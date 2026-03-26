<input type="text" id="msg" placeholder="Enter message">
<button onclick="startPost()">Start POST Stream</button>

<div id="output"></div>

<script>
function startPost() {
    document.getElementById('output').innerHTML = '';

    let message = document.getElementById('msg').value;

    fetch('/stream-post', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => {
        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        function read() {
            reader.read().then(({ done, value }) => {
                if (done) return;

                let chunk = decoder.decode(value);

                // 🔥 split SSE lines
                let lines = chunk.split("\n");

                lines.forEach(line => {
                    if (line.startsWith("data: ")) {
                        let text = line.replace("data: ", "");

                        if (text === "[DONE]") return;

                        document.getElementById('output').innerHTML += text + "<br>";
                    }
                });

                read();
            });
        }

        read();
    });
}
</script>