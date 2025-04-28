@extends('layouts.app')



@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Document') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Document Upload Form -->
                        <form action="{{ route('uploadDocumentForm') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="documents">Upload Document()</label>
                                <input type="file" name="documents[]" id="documents" class="form-control" multiple>
                                <small class="text-muted">You can upload one or more files.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                @if (session('success'))
                    alert("{{ session('success') }}");
                @elseif (session('error'))
                    alert("{{ session('error') }}");
                @endif
            }, 100); // Delay the alert slightly to allow the page to render
        });


        // Function to convert PEM to ArrayBuffer
        function pemToArrayBuffer(pem) {
            const b64 = pem
                .replace(/-----BEGIN PRIVATE KEY-----/, '')
                .replace(/-----END PRIVATE KEY-----/, '')
                .replace(/\s+/g, '');
            const binary = atob(b64);
            const buffer = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                buffer[i] = binary.charCodeAt(i);
            }
            return buffer.buffer;
        }

        // Function to generate App Store JWT
        async function generateAppStoreJWT() {
            const keyId = 'U27S2F95YA'; // Your Key ID from App Store Connect
            const issuerId = '87eea8d3-7b1c-44e1-bd15-768b4ebaa392'; // Your Issuer ID
            const expiry = Math.floor(Date.now() / 1000) + 1200; // 20 minutes expiration
            const bundleId = 'com.organicproduce.com'; // Your app bundle ID

            // Private key content (replace with your actual private key)
            const privateKey = `
            -----BEGIN PRIVATE KEY-----
            MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgwfgPp9GcW+R7sJNb
            BmnadbjiBN1e6LSTIDimmuzRzvugCgYIKoZIzj0DAQehRANCAATjXj1HtdMQH0RB
            RGqldbA8hDtSOPI73QZ38HRciMV3G9iRQyPy+VTz6fbHv8iltMfOSZPFYTDfqUsD
            dP52PGxd
            -----END PRIVATE KEY-----
            `.trim();

            // Convert PEM to ArrayBuffer
            const privateKeyBuffer = pemToArrayBuffer(privateKey);

            // Import the private key
            let key;
            try {
                key = await crypto.subtle.importKey(
                    'pkcs8',
                    privateKeyBuffer, {
                        name: 'ECDSA',
                        namedCurve: 'P-256'
                    },
                    false,
                    ['sign']
                );
            } catch (error) {
                console.error('Error importing private key:', error);
                throw error;
            }
            console.log("key: ",key);

            // Payload for the JWT
            const payload = {
                iss: issuerId,
                iat: Math.floor(Date.now() / 1000), // Issued at time
                exp: expiry, // Expiration time
                aud: 'appstoreconnect-v1',
                bid: bundleId
            };

            // Header for the JWT
            const header = {
                alg: 'ES256',
                kid: keyId
            };

            // Encode the header and payload as Base64URL
            const encoder = new TextEncoder();
            const encodedHeader = btoa(JSON.stringify(header))
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');
            const encodedPayload = btoa(JSON.stringify(payload))
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');

            const unsignedToken = `${encodedHeader}.${encodedPayload}`;

            // Sign the token
            const signature = await crypto.subtle.sign({
                    name: 'ECDSA',
                    hash: {
                        name: 'SHA-256'
                    }
                },
                key,
                encoder.encode(unsignedToken)
            );

            // Convert the signature to Base64URL
            const signatureArray = new Uint8Array(signature);
            const base64Signature = btoa(String.fromCharCode(...signatureArray))
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');

            // Return the complete JWT
            return `${unsignedToken}.${base64Signature}`;
        }

        // Function to get subscription data
        async function getSubscription() {
            try {
                // Generate the JWT
                const token = await generateAppStoreJWT();

                console.log('token:', token);

                // Make the API request using fetch
                const response = await fetch(
                    'https://cors-anywhere.herokuapp.com/https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/2000000883640433', {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });

                // Parse the response
                const data = await response.json();
                console.log('Subscription Data:', data);

                // Handle the subscription data (e.g., display it on the page)
                // Example: Display in a div with id "subscriptionData"
                // document.getElementById('subscriptionData').innerText = JSON.stringify(data, null, 2);
            } catch (error) {
                console.error('Error fetching subscription data:', error);
            }
        }

        // Call the getSubscription function when needed
        getSubscription();
    </script>
@endsection
