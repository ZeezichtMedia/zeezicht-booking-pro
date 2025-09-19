/* eslint-disable no-param-reassign */
import { faker } from '@faker-js/faker';
import { RANDOMIZE } from '../app/constants.js';
import type { Users } from '../types/entities.js';

import usersStaticJSON from '../../data/users.json' assert { type: 'json' };

const usersStaticData: Users = usersStaticJSON;

export function getUsers(randomize = RANDOMIZE) {
	console.log('getUsers - using database API');
	
	// Deze functie wordt niet meer gebruikt - data komt direct van PHP API
	// Maar we houden hem voor backward compatibility
	return [] as Users;
}
