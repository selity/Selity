#!/usr/bin/make -f

# Selity - When virtual hosting becomes scalable
# Copyright 2012-2014 by Selity
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# @category		Selity
# @copyright	2012 by Selity | http://selity.org
# @author		Daniel Andreca <sci2tech@gmail.com>
# @link			http://selity.org Selity Home Site
# @license		http://www.gnu.org/licenses/gpl-2.0.html GPL v2

EXEC = selity_daemon

CC = gcc -ansi -Wall -Wstrict-prototypes -pedantic $(CFLAGS)

CFLAGS = -g -O2

OBJECTS = $(EXEC).o message.o daemon_init.o say.o signal-handlers.o\
receive_data.o send_data.o take_connection.o send_line.o recv_line.o\
helo_cmd.o helo_syntax.o lr_cmd.o lr_syntax.o bye_cmd.o bye_syntax.o

$(EXEC): clean $(OBJECTS)
	$(CC) $(CFLAGS) $(OBJECTS) -o $@
	strip -s $@
	@echo "Compilation done"

%.o : %.c
	$(CC) -c $(CFLAGS) $<

clean:
	rm -f *.o *~ $(EXEC)

.PHONY: clean
