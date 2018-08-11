import os
import sys

MAP = {
    'а': 'a',
    'б': 'b',
    'в': 'v',
    'г': 'g',
    'д': 'd',
    'е': 'e',
    'ж': 'zh',
    'з': 'z',
    'и': 'i',
    'й': '',
    'ĭ': '',
    'к': 'k',
    'л': 'l',
    'м': 'm',
    'н': 'n',
    'о': 'o',
    'п': 'p',
    'р': 'r',
    'с': 's',
    'т': 't',
    'у': 'u',
    'ф': 'f',
    'х': 'h',
    'ц': 'c',
    'ч': 'ch',
    'ш': 'sh',
    'щ': 'sh',
    'э': 'e',
    'ю': 'u',
    'я': 'ya',
    'ы': 'i',
    'ъ': '',
    'ь': '',
}


def translit_folder():
    path = os.getcwd()
    filenames = os.listdir(path)
    for filename in filenames:
        if filename == __file__ or filename == '.' or filename == '..':
            continue
        new_name = []
        for c in filename:
            if c.lower() not in MAP:
                new_name.append(c)
            else:
                new_name.append(MAP[c.lower()])
        os.rename(filename, "".join(new_name))
        print("'{}' -> '{}'".format(filename, "".join(new_name)))


def translit_file(filename):
    with open(filename, 'r') as file:
        filedata = file.read()
    new_data = []
    for c in filedata:
        if c.lower() not in MAP:
            new_data.append(c)
        else:
            new_data.append(MAP[c.lower()])
    with open(filename, 'w') as file:
        file.write("".join(new_data))


if len(sys.argv) > 1:
    translit_file(sys.argv[1])
else:
    translit_folder()
