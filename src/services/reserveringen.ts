import type { Reserveringen } from '../types/entities.js';

export function getReserveringen() {
	console.log('getReserveringen - using database API');
	
	// Deze functie wordt niet meer gebruikt - data komt direct van PHP API
	// Maar we houden hem voor backward compatibility
	return [] as Reserveringen;
}
