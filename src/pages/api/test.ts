import type { APIRoute } from 'astro';

export const GET: APIRoute = () => {
	return new Response(JSON.stringify({ message: 'API is working!' }), {
		status: 200,
		headers: {
			'Content-Type': 'application/json',
		},
	});
};

export const POST: APIRoute = async ({ request }) => {
	const body = await request.json();
	return new Response(JSON.stringify({ 
		message: 'POST received!', 
		data: body 
	}), {
		status: 200,
		headers: {
			'Content-Type': 'application/json',
		},
	});
};
