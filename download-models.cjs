const https = require('https');
const fs = require('fs');
const path = require('path');

// Ensure models directory exists
const modelsDir = path.join(__dirname, 'public', 'models');
if (!fs.existsSync(modelsDir)) {
    fs.mkdirSync(modelsDir, { recursive: true });
}

// Model files to download
const models = [
    {
        name: 'tiny_face_detector_model-weights_manifest.json',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/tiny_face_detector_model-weights_manifest.json'
    },
    {
        name: 'tiny_face_detector_model-shard1.bin',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/tiny_face_detector_model-shard1.bin'
    },
    {
        name: 'face_landmark_68_model-weights_manifest.json',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_landmark_68_model-weights_manifest.json'
    },
    {
        name: 'face_landmark_68_model-shard1.bin',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_landmark_68_model-shard1.bin'
    },
    {
        name: 'face_recognition_model-weights_manifest.json',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_recognition_model-weights_manifest.json'
    },
    {
        name: 'face_recognition_model-shard1.bin',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_recognition_model-shard1.bin'
    },
    {
        name: 'face_recognition_model-shard2.bin',
        url: 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_recognition_model-shard2.bin'
    }
];

function downloadFile(url, dest) {
    return new Promise((resolve, reject) => {
        const file = fs.createWriteStream(dest);

        https.get(url, (response) => {
            if (response.statusCode === 200) {
                response.pipe(file);
                file.on('finish', () => {
                    file.close();
                    console.log(`✅ Downloaded: ${path.basename(dest)}`);
                    resolve();
                });
            } else {
                reject(new Error(`HTTP ${response.statusCode}: ${url}`));
            }
        }).on('error', (err) => {
            fs.unlink(dest, () => {}); // Delete partial file
            reject(err);
        });
    });
}

async function downloadModels() {
    console.log('🚀 Downloading face-api.js model files...\n');

    for (const model of models) {
        const dest = path.join(modelsDir, model.name);

        try {
            await downloadFile(model.url, dest);
        } catch (error) {
            console.error(`❌ Error downloading ${model.name}:`, error.message);
        }
    }

    console.log('\n✅ Model download completed!');
    console.log(`📁 Models saved to: ${modelsDir}`);
}

downloadModels();