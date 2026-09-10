<?php
// Debug script to attach to my-profile page and capture console output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Console Output Capture</title>
    <style>
        body {font-family: monospace; padding: 20px;}
        #console-output {background: #1e1e1e; color: #00ff00; padding: 20px; border-radius: 8px; max-height: 600px; overflow-y: auto; margin-bottom: 20px;}
        .log {padding: 2px 0;}
        .warn {color: #ffaa00;}
        .error {color: #ff4444;}
        button {padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 20px;}
    </style>
</head>
<body>
    <h1>Console Output Debugger</h1>
    <button onclick="openProfile()">Click Edit Profile Button</button>
    <button onclick="clearOutput()">Clear Output</button>
    <div id="console-output"></div>
    
    <iframe id="profile-frame" src="http://localhost:8080/pages/my-profile.php" style="width:100%; height:800px; border:1px solid #ccc;"></iframe>
    
    <script>
        const output = document.getElementById('console-output');
        
        function log(type, msg) {
            const line = document.createElement('div');
            line.className = `log ${type}`;
            line.textContent = `[${type.toUpperCase()}] ${msg}`;
            output.appendChild(line);
            output.scrollTop = output.scrollHeight;
        }
        
        function clearOutput() {
            output.innerHTML = '';
        }
        
        // Connect to iframe console
        const frame = document.getElementById('profile-frame');
        frame.onload = function() {
            try {
                const iframeConsole = frame.contentWindow.console;
                const originalLog = iframeConsole.log;
                const originalWarn = iframeConsole.warn;
                const originalError = iframeConsole.error;
                
                iframeConsole.log = function(...args) {
                    log('log', args.join(' '));
                    originalLog.apply(iframeConsole, args);
                };
                
                iframeConsole.warn = function(...args) {
                    log('warn', args.join(' '));
                    originalWarn.apply(iframeConsole, args);
                };
                
                iframeConsole.error = function(...args) {
                    log('error', args.join(' '));
                    originalError.apply(iframeConsole, args);
                };
                
                log('log', 'Console capture initialized');
            } catch (e) {
                log('error', 'Could not capture iframe console: ' + e.message);
            }
        };
        
        function openProfile() {
            try {
                frame.contentWindow.document.querySelector('button[data-profile-open]').click();
                log('log', 'Clicked Edit Profile button');
            } catch (e) {
                log('error', 'Error clicking button: ' + e.message);
            }
        }
    </script>
</body>
</html>
