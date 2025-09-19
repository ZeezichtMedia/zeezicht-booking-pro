import type { endpointsToOperations } from '../pages/api/[...entity].js';
import type { playgroundActions } from '../pages/playground/_actions.js';

export type EndpointsToOperations = typeof endpointsToOperations;
export type Endpoint = keyof EndpointsToOperations;

export type Products = Product[];
export interface Product {
	name: string;
	category: string;
	technology: string;
	id: number;
	description: string;
	price: string;
	discount: string;
}

export type Users = User[];
export interface User {
	id: number;
	name: string;
	avatar: string;
	email: string;
	biography: string;
	position: string;
	country: string;
	status: string;
}

export type Reserveringen = Reservering[];
export interface Reservering {
	id: number;
	gast_naam: string;
	kamer: string;
	check_in: string;
	check_out: string;
	aantal_gasten: number;
	totaal_prijs: string;
	status: string;
	email: string;
	telefoon: string;
	opmerkingen: string;
}

export type PlaygroundAction = (typeof playgroundActions)[number];
