/// @file:   db.js
/// @author: John Ed Quinn
/// @desc:   NA
/// @notes:  NA

/*
Questions for Prof:
- When 'conv' is passed to addConv and updateConv, are messages part of the object?
- For the 'conv' functions, what is the token? How do we identify which conversation to update?
    - Do I check if the user is a member of the conversation before allowing the update?
- Can I update some pieces of a user/conversation at a time?
- In addUser, why use 'cb' for some errors but then throw new Errors other times?
- Should UserIDs really be Date.now()? I've done it for convs and msgs
- Should messages be an Array or a Dictionary?
*/

var users = {
    '1': {
        id: '1',
        email: 'john',
        name: 'John',
        surname: 'Quinn',
        password: 'john',
        img: ''
    },
    '2': {
        id: '2',
        email: 'max',
        name: 'Max',
        surname: 'Smith',
        password: 'max',
        img: ''
    },
    '3': {
        id: '3',
        email: 'jake',
        name: 'Jake',
        surname: 'Smith',
        password: 'jake',
        img: ''
    },
    '4': {
        id: '4',
        email: 'joe',
        name: 'Joe',
        surname: 'Smith',
        password: 'joe',
        img: ''
    }
};

var conversations= {
    '1': {
        id: '1',
        name: 'Conv1',
        img: '',
        members: ['1', '3', '2'],
        messages: [
            { id: '1', ts: Date.now(), author: '3', content: 'Hi.' },
            { id: '2', ts: Date.now(), author: '2', content: 'Hi there. I was hoping you could help me with something. It wont.' },
            { id: '3', ts: Date.now(), author: '1', content: 'Got it.' }
        ]
    },
    '2': {
        id: '2',
        name: 'Conv2',
        img: '',
        members: ['1', '2'],
        messages: [
            { id: '1', ts: Date.now(), author: '1', content: 'Hi 1' },
            { id: '2', ts: Date.now(), author: '2', content: 'Hi 2' }
        ]
    }
};

/// @func:  addUser
/// @param: user - NA
/// @param: cb - NA
/// @desc:  NA
function addUser (user, cb) {
    console.log('addUser(' + JSON.stringify(user) + ')');

    // Check User Argument
    if (!user) throw new Error('User not specified');
    if (!user.email || !user.name || !user.surname || !user.password) throw new Error('Missing user data');

    // Check Unique Email
    for (var id in users) {
        if (users[id].email == user.email) {
            if (cb) cb(new Error('User already exists'));
            return;
        }
    }

    // Create New User Variable
    var newUser = {
        email: user.email,
        name: user.name,
        surname: user.surname,
        password: user.password,
        img: user.img
    }
    newUser.id = String(Date.now());

    // Add New User
    users[newUser.id] = newUser;
    user.id = newUser.id;

    if (cb) cb(null, user);
}

/// @func:  login
/// @param: email - NA
/// @param: password - NA
/// @param: cb - NA
/// @desc:  NA
function login (email, password, cb) {
    console.log('login()');

    // Check Arguments
    if (!email) throw new Error('Email not specified');
    if (!password) throw new Error('Password not specified');

    for (var id in users) {
        if (users[id].email == email) {
            if (users[id].password == password) {
                var user = {
                    id: id,
                    email: users[id].email,
                    name: users[id].name,
                    surname: users[id].surname,
                    img: users[id].img
                };
                if (cb) cb(null, id, user);
                return;
            } else {
                if (cb) cb(new Error('Wrong password'));
            }
        }
    }
    if (cb) cb(new Error('Wrong credentials'));
}

/// @func:  listUsers
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function listUsers (token, query, cb) {
    console.log('listUsers()');
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    //
    var results = [];
    for (var id in users) {
        var matches = !query || !Object.keys(query).length;
        if (!matches) {
            for (var cond in query) {
                if (String(users[id][cond]).indexOf(String(query[cond])) != -1) {
                    matches = true;
                    break;
                }
            }
        }
        if (matches) results.push({
            id: users[id].id,
            email: users[id].email,
            name: users[id].name,
            surname: users[id].surname,
            img: users[id].img
        });
    }

    //
    if (cb) cb(null, results);
}

/// @func:  updateUser
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function updateUser (token, user, cb) {
    console.log('updateUser(' + JSON.stringify(user) + ')');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check User Parameter
    if (!user) throw new Error('User not specified');
    if (!user.email || !user.name || !user.surname || !user.password) throw new Error('Missing user data');

    // Make Sure Unique Email
    for (var id in users) {
        if (users[id].email == user.email && users[id].id != token) {
            if (cb) cb(new Error('Email Already Taken'));
            return;
        }
    }

    // Set User Information
    users[token].email = user.email;
    users[token].name = user.name;
    users[token].surname = user.surname;
    users[token].img = user.img;

    if (cb) cb(null, users[token]);
}

/// @func:  addConversation
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function addConversation (token, conv, cb) {
    console.log('addConversation(' + JSON.stringify(conv) + ')');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation Exists
    if (!conv) throw new Error('Conversation Not Specified');
    if (!conv.name || !conv.img || !conv.members) throw new Error('Missing Conversation Data');

    // Create New Conversation Variable
    var newConv = {
        name: conv.name,
        img: conv.img,
        members: conv.members
    }
    newConv.messages = [];
    newConv.id = String(Date.now());

    // Add New Conversation to Conversations
    conversations[newConv.id] = newConv;
    conv.id = newConv.id;

    if (cb) cb(null, conv);
}

/// @func:  updateConversation
/// @param: token - NA
/// @param: conv - NA
/// @param: cb - NA
/// @desc:  NA
function updateConversation (token, conv, cb) {
    console.log('updateConversation(' + JSON.stringify(conv) + ')');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conv Argument Exists
    if (!conv) throw new Error('Conversation Not Specified');
    
    // Check Conversation Exists
    var convId = conversations[conv.id] ? conv.id : null;
    if (!convId) {
        if (cb) cb(new Error('No Conversation with Specified ID'));
        return;
    }

    // Check Conv Attributes
    if (!conv.name || !conv.img || !conv.members || !conv.messages) throw new Error('Missing Conversation Data');

    // Check User is Member in Conversation
    var callerInConv = false;
    for (var member in conversations[convId]['members'])
        if (userId == member) callerInConv = true;
    if (!callerInConv) {
        if (cb) cb(new Error('Caller Not in Conversation'));
        return;
    }

    // Set Conv Information
    conversations[convId].name = conv.name;
    conversations[convId].img = conv.img;
    conversations[convId].members = conv.members;
    conversations[convId].messages = conv.messages;

    if (cb) cb(null, users[token]);
}

/// @func:  listConversations
/// @param: token - NA
/// @param: query - NA
/// @param: cb - NA
/// @desc:  NA
function listConversations (token, query, cb) {
    console.log('listConversations()');

    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    //
    var results = [];
    for (var id in conversations) {
        var matches = !query || !Object.keys(query).length;
        if (!matches) {
            for (var cond in query) {
                if (String(conversations[id][cond]).indexOf(String(query[cond])) != -1) {
                    matches = true;
                    break;
                }
            }
        }
        if (matches) results.push({
            id: conversations[id].id,
            name: conversations[id].name,
            img: conversations[id].img,
            members: conversations[id].members,
            messages: conversations[id].messages
        });
    }

    if (cb) cb(null, results);
}

/// @func:  joinConversation
/// @param: token - NA
/// @param: convId - NA
/// @param: usrId - NA
/// @param: cb - NA
/// @desc:  NA
function joinConversation (token, convId, usrId, cb) {
    console.log('joinConversation(' + convId + ',' + usrId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    // Check Conversation Exists
    if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }

    // Check Joined User ID Argument Exists
    if (!usrId) {
        if (cb) cb(new Error('Missing User ID'));
        return;
    }

    // Check Joined User Exists
    if (!users[usrId]) {
        if (cb) cb(new Error('User Does Not Exist'));
        return;
    }

    // Check Calling User is Member and Joining Member is not
    var calledUserInMembers = false;
    var joinedUserInMembers = false;
    for (var id in conversations[convId].members) {
        if (id == token) calledUserInMembers = true;
        if (id == usrId) joinedUserInMembers = true;
    }
    if (!calledUserInMembers) {
        if (cb) cb(new Error('Calling User Not in Conversation'));
        return;
    }
    if (joinedUserInMembers) {
        if (cb) cb(new Error('Joining User Already in Conversation'));
        return;
    }

    // Add Member to Conversation Members Array
    conversations[convId].members.push(usrId);
    if (cb) cb(null, conversations[convId]);
}

/// @func:  leaveConversation
/// @param: token - NA
/// @param: convId - NA
/// @param: cb - NA
/// @desc:  NA
function leaveConversation (token, convId, cb) {
    console.log('leaveConversation(' + convId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    // Check Conversation Exists
    if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }

    // Check Calling User is Member
    var userInMembers = false;
    for (id in conversations[convId].members) {
        if (id == token) userInMembers = true;
    }
    if (!userInMembers) {
        if (cb) cb(new Error('User Not in Conversation'));
        return;
    }

    // Remove User from Members Array
    conversations[convId].members.splice(conversation[convId].members.indexOf(userId));
    if (cb) cb(null);
}

/// @func:  addMessage
/// @param: token - NA
/// @param: convId - NA
/// @param: content - NA
/// @param: cb - NA
/// @desc:  NA
function addMessage (token, convId, content, cb) {
    console.log('addMessage(' + convId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    // Check Conversation Exists
    if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }

    // Check Calling User is Member
    var userInMembers = false;
    for (id in conversations[convId].members) {
        if (id == token) userInMembers = true;
    }
    if (!userInMembers) {
        if (cb) cb(new Error('User Not in Conversation'));
        return;
    }

    // Check Content Argument Exists
    if (!content) {
        if (cb) cb(new Error('Missing Message Content'));
        return;
    }

    // Create New User Variable
    var newMsg = {
        ts: Date.now(),
        author: userId,
        content: content
    }
    newMsg.id = String(Date.now());

    // Add New User
    conversations[convId].messages.push(newMsg);
    if (cb) cb(null, newMsg);
}

/// @func:  removeMessage
/// @param: token - NA
/// @param: convId - NA
/// @param: msgId - NA
/// @param: cb - NA
/// @desc:  NA
function removeMessage (token, convId, msgId, cb) {
    console.log('removeMessage(' + convId + ',' + msgId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    // Check Conversation Exists
    if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }
    
    // Check Calling User is Member
    var userInMembers = false;
    for (id in conversations[convId].members) {
        if (id == token) userInMembers = true;
    }
    if (!userInMembers) {
        if (cb) cb(new Error('User Not in Conversation'));
        return;
    }

    // Check Message ID Argument Exists
    if (!msgId) {
        if (cb) cb(new Error('Missing Message ID'));
        return;
    }

    // Find Message Index
    var msgIndex = 0; var messageFound = false;
    for (var message in conversations[convId].messages) {
        if (message.id == msgId) { messageFound = true; break; }
        msgIndex++;
    }

    // Check Message Exists
    if (!messageFound) {
        if (cb) cb(new Error('Message in Conversation Does Not Exist'));
        return;
    }

    // Check Message Author is Calling User
    if (conversations[convId].messages[msgIndex].author != userId) {
        if (cb) cb(new Error('User Not Author of Message'));
        return;
    }

    // Remove Message
    conversations[convId].messages.splice(msgIndex, 1);

    if (cb) cb(null);
}

/// @func:  listMessages
/// @param: token - NA
/// @param: convId - NA
/// @param: ini - NA
/// @param: end - NA
/// @param: cb - NA
/// @desc:  NA
function listMessages (token, convId, ini, end, cb) {
    console.log('listMessages(' + convId + ')');
    
    // Check Token Exists
    if (!token) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Valid Token
    var userId = users[token] ? token : null;
    if (!userId) {
        if (cb) cb(new Error('Invalid Token'));
        return;
    }

    // Check Conversation ID Argument Exists
    if (!convId) {
        if (cb) cb(new Error('Missing Conversation ID'));
        return;
    }

    // Check Conversation Exists
    if (!conversations[convId]) {
        if (cb) cb(new Error('Conversation Does Not Exist'));
        return;
    }
    
    // Check Calling User is Member
    var userInMembers = false;
    for (id in conversations[convId].members) {
        if (id == token) userInMembers = true;
    }
    if (!userInMembers) {
        if (cb) cb(new Error('User Not in Conversation'));
        return;
    }

    if (cb) cb(null, conversations[convId].messages.slice(ini, end));
}

export default {
    addUser,
    listUsers,
    updateUser,
    login,
    addConversation,
    updateConversation,
    listConversations,
    joinConversation,
    leaveConversation,
    addMessage,
    removeMessage,
    listMessages
}