mod hlt;

use hlt::Location;
use hlt::GameMap;
use std::io;
use std::collections;
use std::collections::HashMap;
use std::string;
use std::io::Write;
use std::str::FromStr;

//Persistant between moves, that way if the user screws up the map it won't persist.
static mut _width: u16 = 0;
static mut _height: u16 = 0;

fn serialize_move_set(moves: HashMap<Location, u8>) -> String {
	let mut s: String = String::new();
	for (l, d) in moves {
		s = format!("{}{}{}{}{}{}{}", s, l.x.to_string(), " ", l.y.to_string(), " ", d.to_string(), " ");
	}
	s
}

fn deserialize_map_size(s: String) -> () {
	let splt: Vec<&str> = s.split(" ").collect();
	unsafe {
		_width = u16::from_str(splt[0]).unwrap();
		_height = u16::from_str(splt[1]).unwrap();
	}
}

fn deserialize_productions(s: String) -> hlt::GameMap {
	let splt: Vec<&str> = s.split(" ").collect();
	let mut gmp = hlt::GameMap { width: 0, height: 0, contents: Vec::new() };
	unsafe {
		gmp.width = _width;
		gmp.height= _height;
		gmp.contents.resize(gmp.height as usize, Vec::new());
		let mut loc = 0;
		for y in 0.._height {
			for x in 0.._width {
				gmp.contents[y as usize].push(hlt::Site::new(u8::from_str(splt[loc]).unwrap()));
				loc += 1;
			}
		}
	}
	gmp
}

fn deserialize_map(s: String, gmp: &mut hlt:: GameMap) -> () {
	let splt: Vec<&str> = s.split(" ").collect();
	unsafe {
		let mut counter = 0;
		let mut owner = 0;
		let mut loc: usize = 0;
		for a in 0.._height {
			for b in 0.._width {
				if counter == 0 {
					counter = u8::from_str(splt[loc]).unwrap();
					loc += 1;
					owner = u8::from_str(splt[loc]).unwrap();
					loc += 1;
				}
				gmp.get_site(hlt::Location { x: a, y: b }, hlt::STILL).owner = owner;
				counter -= 1;
			}
		}
		for a in 0.._height {
			for b in 0.._width {
				gmp.get_site(hlt::Location { x: a, y: b }, hlt::STILL).strength = u8::from_str(splt[loc]).unwrap();
				loc += 1;
			}
		}
	}
}


fn send_string(s: String) -> () {
	println!("{}", s);
	io::stdout().flush();
}

fn get_string() -> String {
	let mut s = String::new();
	io::stdin().read_line(&mut s);
	s.trim();
	s
}

fn get_init() -> (u8, hlt::GameMap) {
	let playerTag: u8 = u8::from_str(&get_string()).unwrap();
	deserialize_map_size(get_string());
	let mut gmp = deserialize_productions(get_string());
	deserialize_map(get_string(), &mut gmp);
	(playerTag, gmp)
}

fn send_init_response(name: String) -> () {
	send_string(name);
}

fn get_frame(gmp: &mut hlt::GameMap) -> () {
	deserialize_map(get_string(), gmp);
}

fn send_frame(moves: HashMap<hlt::Location, u8>) -> () {
	send_string(serialize_move_set(moves));
}

fn main() {
	println!("Hello World!");
}