export async function searchOpenStreetMap(query) {

    const url =
        `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&q=${encodeURIComponent(query)}&limit=5`;

try {
    const response = await fetch(url,{
    headers: {
            "Accept-Language": "fr"
        }
    });
    console.log(url);

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return await response.json();
} catch (error) {
    console.error("Erreur OpenStreetMap :", error);
    return [];
}
}