document.addEventListener("DOMContentLoaded", function () {
  const chatToggle = document.getElementById("bch-livechat-toggle");
  const chatBox = document.getElementById("bch-livechat-box");
  const closeBtn = document.getElementById("bch-livechat-close");
  const sendBtn = document.getElementById("bch-livechat-send");
  const inputField = document.getElementById("bch-livechat-input");
  const messagesBox = document.getElementById("bch-livechat-messages");

  // Toggle chat visibility
  function toggleChatBox() {
    const isHidden = chatBox.classList.toggle("bch-chat-hidden");
    chatBox.setAttribute("aria-bch-chat-hidden", isHidden);
    if (!isHidden) {
      inputField.focus();
    }
  }

  chatToggle.addEventListener("click", toggleChatBox);

  closeBtn.addEventListener("click", function (e) {
    if (e) e.preventDefault();
    chatBox.classList.add("bch-chat-hidden");
    chatBox.setAttribute("aria-bch-chat-hidden", "true");
  });

  // Send message
  // Track last topic for context-aware replies
  let lastTopic = null;
  let userTimeOfDay = null; // Tracks user's preferred time of day (morning, afternoon, evening)

  // Improved sendMessage: always show both user and agent messages, prevent duplicate answers
  function sendMessage(quickReplyText) {
    const message = quickReplyText || inputField.value.trim();
    if (!message) return;
    appendMessage("user", message);
    inputField.value = "";
    inputField.focus();
    showTypingIndicator();
    setTimeout(() => {
      removeTypingIndicator();
      const reply = getAutoReply(message, lastTopic, userTimeOfDay);
      // Prevent duplicate bot replies (do not reply if last bot message is identical)
      const lastAgentMsg = [...messagesBox.querySelectorAll('.bch-chat-msg.agent span')].pop();
      if (!lastAgentMsg || lastAgentMsg.innerHTML !== reply.text) {
        // Add quick replies for certain topics
        let quickReplies = null;
        if (reply.nextTopic === "courses") {
          quickReplies = ["Frontend Development Course", "Backend Development Course", "Full Stack Development Course", "Fee Breakdown"];
        } else if (reply.nextTopic === "payment") {
          quickReplies = ["Fee Breakdown", "Discounts", "Register"];
        }
        appendMessage("agent", reply.text, quickReplies);
      }
      lastTopic = reply.nextTopic;
      if (reply.userTimeOfDay) userTimeOfDay = reply.userTimeOfDay;
    }, 800);
  }

  // Determine auto-reply based on user message
  function getAutoReply(userMsg, lastTopic, userTimeOfDay) {
    // Defensive: ensure userMsg is a string
    if (typeof userMsg !== 'string') {
      userMsg = userMsg && userMsg.text ? String(userMsg.text) : String(userMsg);
    }
    // Helper for typo-tolerant matching
    function fuzzyMatch(str, words) {
      return words.some(word => {
        const re = new RegExp(word.split('').join('.?'), 'i');
        return re.test(str);
      });
    }
    const msg = userMsg.toLowerCase();
    // Gratitude
    if (fuzzyMatch(msg, ["thank you", "thanks", "thank u", "appreciate it", "much appreciated"])) {
      return {
        text: "You’re most welcome! If you have more questions, just let me know—I’m always here to help.",
        nextTopic: null
      };
    }
    // Farewell
    if (fuzzyMatch(msg, ["bye", "goodbye", "see you", "see ya", "later", "farewell"])) {
      return {
        text: "Goodbye! If you have more questions, feel free to chat with us anytime.",
        nextTopic: null
      };
    }
    // Friendly/fun responses
    if (fuzzyMatch(msg, ["amazing", "awesome", "cool", "great", "fantastic"])) {
      return {
        text: "Right? We love what we do at BCH! If you want to know more or get involved, just ask.",
        nextTopic: null
      };
    }
    if (fuzzyMatch(msg, ["we meet again", "hello again", "back again"])) {
      return {
        text: "Welcome back! It’s always nice to chat with you. What can I help you with this time?",
        nextTopic: null
      };
    }
    // What do you know/can I ask
    if (fuzzyMatch(msg, ["what do you know", "what can you do", "what can i ask", "what can i aask you", "what topics"])) {
      return {
        text: "You can ask me about BCH courses, schedules, pricing, instructors, services (laptops, software, cybersecurity), events, support, and more! If you’re curious, just ask—I’ll do my best to help.",
        nextTopic: null
      };
    }
    // Playful/self-aware for 'you don’t know so many things'
    if (fuzzyMatch(msg, ["you don’t know so many things", "you dont know so many things", "you don't know much", "you know nothing"])) {
      return {
        text: "I’m always learning and getting better! If there’s something I’m missing, let me know—or I can connect you with a human agent.",
        nextTopic: null
      };
    }
    // Fun fact / playful
    if (fuzzyMatch(msg, ["tell me a fun fact", "fun fact", "something cool", "did you know"])) {
      return {
        text: "Did you know? Bonnie Computer Hub has helped over 2,000 students launch their tech careers! 🚀 If you want to be next, just ask about our courses or events.",
        nextTopic: null
      };
    }
    // Jokes and humor
    if (fuzzyMatch(msg, ["tell me a joke", "make me laugh", "joke"])) {
      return {
        text: "Why did the computer go to art school? Because it wanted to learn how to draw its curtains! 😄 If you want more tech jokes, just ask!",
        nextTopic: null
      };
    }
    // Encouragement
    if (fuzzyMatch(msg, ["encourage me", "motivate me", "inspire me"])) {
      return {
        text: "You’ve got this! Every tech expert started as a beginner. Keep learning, and you’ll amaze yourself! 💪",
        nextTopic: null
      };
    }
    // Small talk & empathy
    if (fuzzyMatch(msg, ["how are you", "how's it going", "how are u", "how do you do"])) {
      return {
        text: "Thanks for asking! I’m just a virtual assistant, but I’m always happy to help. How can I assist you today?",
        nextTopic: null
      };
    }
    if (fuzzyMatch(msg, ["tired", "i am tired", "j am tired", "i'm tired", "feeling tired"])) {
      return {
        text: "Sorry to hear you’re tired! Remember to take breaks and take care of yourself. If there’s anything I can do to make things easier, just let me know!",
        nextTopic: null
      };
    }
    if (fuzzyMatch(msg, ["do you love people", "do you like people", "do you have feelings", "are you human"])) {
      return {
        text: "I don’t have feelings like humans do, but I do my best to be friendly and helpful! 😊",
        nextTopic: null
      };
    }
    if (fuzzyMatch(msg, ["my friend", "buddy", "pal", "mate"])) {
      return {
        text: "You got it, friend! If you need anything, just ask.",
        nextTopic: null
      };
    }
    // Playful/self-aware responses
    if (fuzzyMatch(msg, ["you need more training", "need more training", "learn more", "get smarter"])) {
      return {
        text: "I’m always learning! If there’s something I can do better, let me know—or you can ask for a human agent anytime.",
        nextTopic: null
      };
    }
    // Bot scope
    if (fuzzyMatch(msg, ["can you answer questions beyond", "outside bch", "beyond bch", "other topics"])) {
      return {
        text: "I’m here to help with anything related to Bonnie Computer Hub—courses, products, services, and support! For other topics, I recommend searching online or asking a human agent.",
        nextTopic: null
      };
    }
    // Negative affirmation (no)
    if (/^(no|nope|nah|not now|not really)\b/.test(msg)) {
      return {
        text: "That’s okay! If you have any other questions or need help, just let me know.",
        nextTopic: null
      };
    }
    // Affirmative/confirmation replies
    if (/^(yes|yeah|yep|sure|okay|ok|of course|please|alright|yup|certainly|why not)\b/.test(msg)) {
      if (lastTopic === "courses") {
        return {
          text: "Awesome! Here’s a link to our current course catalog: <a href='courses.php' target='_blank'>View Courses</a>. If you have a course in mind or need recommendations, just let me know!",
          nextTopic: null
        };
      }
      if (lastTopic === "payment") {
        return {
          text: "No problem! You can make payments securely via our online portal. Would you like me to send you a direct payment link or walk you through the process?",
          nextTopic: null
        };
      }
      if (lastTopic === "software" || lastTopic === "webdev") {
        return {
          text: "Great! Please share a few details about your software or web development project, and one of our specialists will get in touch to discuss your needs further.",
          nextTopic: null
        };
      }
      if (lastTopic === "greeting") {
        return {
          text: "😊 How can I assist you today?",
          nextTopic: null
        };
      }
      // Add more context-aware responses as needed
      return {
        text: "Great! How else can I assist you?",
        nextTopic: null
      };
    }
    // Handle user corrections about time of day (with typo tolerance)
    if (/not (morning|afternoon|evening|evenig)|it's (not|now) (morning|afternoon|evening|evenig)|it's (morning|afternoon|evening|evenig)/.test(msg)) {
      let match = msg.match(/(morning|afternoon|evening|evenig)/);
      if (match) {
        let tod = match[1];
        if (tod === 'evenig') tod = 'evening';
        return {
          text: `Oh, thanks for correcting me! ${tod.charAt(0).toUpperCase() + tod.slice(1)} it is 😊. How can I help you this ${tod}?`,
          nextTopic: "greeting",
          userTimeOfDay: tod
        };
      }
      return {
        text: "Oops, sorry about that! How can I assist you right now?",
        nextTopic: "greeting"
      };
    }
    // Conversational greetings with user or system time of day (fuzzy match)
    if (fuzzyMatch(msg, ["hi", "hello", "hey", "greetings"])) {
      let greeting;
      if (userTimeOfDay) {
        greeting = `Good ${userTimeOfDay}!`;
      } else {
        greeting = getTimeBasedGreeting();
      }
      // Only use one greeting phrase, avoid duplicating 'How can I help you today?'
      const friendly = [
        `${greeting} How can I help you today?`,
        `${greeting} 😊 How can I assist you?`,
        `${greeting} Hope you're having a great day!`,
        `Hi there! How's your day going?`,
        `Hello! Anything exciting you’re working on?`,
        `Hey! Let me know if you need help with anything!`
      ];
      // Remove accidental duplicates
      const filtered = friendly.filter((s, i, arr) => arr.indexOf(s) === i);
      return {
        text: filtered[Math.floor(Math.random() * filtered.length)],
        nextTopic: "greeting"
      };

    }
    if (fuzzyMatch(msg, ["good morning"])) {
      return { text: "Good morning! Hope you have a productive day. How can I help you?", nextTopic: "greeting", userTimeOfDay: "morning" };
    }
    if (fuzzyMatch(msg, ["good afternoon"])) {
      return { text: "Good afternoon! How can I assist you today?", nextTopic: "greeting", userTimeOfDay: "afternoon" };
    }
    if (fuzzyMatch(msg, ["good evening", "good evenig"])) {
      return { text: "Good evening! Hope your day has been going well. How can I help?", nextTopic: "greeting", userTimeOfDay: "evening" };
    }
    // Info about BCH
    if (/who (are|is) (bch|you)\b|what is bch|about (bch|you)/.test(msg)) {
      return { text: "Bonnie Computer Hub (BCH) is your trusted partner for computer products, software, and IT learning. How can we help you today?", nextTopic: null };
    }
    // BCH location
    if (/where.*(bch|bonnie computer hub).*located|location|address|find.*bch|your location/.test(msg)) {
      return {
        text: "We're located at Nairobi CBD, 3rd Floor, Tech Plaza, Moi Avenue. If you need directions or want to schedule a visit, just let us know!",
        nextTopic: "directions"
      };
    }
    // Directions
    if (fuzzyMatch(msg, ["directions", "how to get there", "route", "way to bch"])) {
      return {
        text: "To get to BCH: Head to Moi Avenue, Tech Plaza, 3rd Floor. If you need step-by-step directions from your location, just let me know your starting point! Would you like to schedule a visit?",
        nextTopic: null
      };
    }
    // Help me login/register (direct guidance)
    if (fuzzyMatch(msg, ["help me login", "help me log in", "help with login", "help logging in"])) {
      return {
        text: "To log in, click the 'Login' button at the top right of our website and enter your username and password. If you have trouble, let me know!",
        nextTopic: null
      };
    }
    if (fuzzyMatch(msg, ["help me register", "help me sign up", "help with registration", "help registering"])) {
      return {
        text: "You can register by clicking the 'Register' or 'Sign Up' button at the top of our website, or by visiting <a href='register.php' target='_blank'>the registration page</a>. If you need help with the process, just let me know!",
        nextTopic: null
      };
    }
    // Help/support (now with empathy)
    if (/help|support|assist|problem|issue|need (help|assistance)/.test(msg) || fuzzyMatch(msg, ["need help", "help me", "can you help", "support", "assistance"])) {
      return {
        text: "No worries, I’m here to help! Please describe your issue or question, and we’ll get it sorted together.",
        nextTopic: null
      };
    }
    // Opening hours and holidays
    if (fuzzyMatch(msg, ["opening hours", "hours of operation", "when are you open", "what time open", "holiday hours", "closed days"])) {
      return {
        text: "We’re open Monday to Friday, 9am–6pm. We’re usually closed on public holidays—if you want to visit on a holiday, let me know and I’ll confirm our schedule!",
        nextTopic: null
      };
    }
    // Payment methods
    if (fuzzyMatch(msg, ["payment methods", "how to pay", "pay with", "mpesa", "credit card", "cash", "payment options"])) {
      return {
        text: "We accept payments via M-Pesa, credit/debit cards, and cash at our office. For online payments, just ask for help and I’ll guide you!",
        nextTopic: null
      };
    }
    // Laptop sales/services
    if (fuzzyMatch(msg, ["laptop sales", "buy laptop", "laptops", "laptop repair", "fix my laptop", "laptop service"])) {
      return {
        text: "We offer a range of laptops for sale and provide expert repair and upgrade services. Looking for a new device or need help with yours? I can connect you with our BCH_LAPTOPS team!",
        nextTopic: null
      };
    }
    // Software/web development services listing
    if (fuzzyMatch(msg, ["software development", "web development", "what software do you offer", "what web development do you offer", "list of software", "list of web development", "development services", "your services"])) {
      return {
        text: `<b>We Offer:</b><br>
        • Custom Website Design & Development<br>
        • E-commerce Solutions<br>
        • Web App Development<br>
        • Mobile App Development<br>
        • Content Management Systems (CMS)<br>
        • API Integration<br>
        • Software Licensing & Installation<br>
        • Maintenance & Support<br>
        • UI/UX Design<br>
        • SEO & Analytics<br>
        • Cybersecurity Solutions<br>
        Want to discuss your project or get a quote? <a href='contact.php' target='_blank'>Contact us here</a> or call 0729-820-689!`,
        nextTopic: null
      };
    }
    // Software licensing/support
    if (fuzzyMatch(msg, ["software", "software support", "software license", "buy software", "install software"])) {
      return {
        text: "Need software or support? BCH_SOFTWARE provides licensing, installation, and troubleshooting for popular programs. Let me know what you need!",
        nextTopic: null
      };
    }
    // Cybersecurity services
    if (fuzzyMatch(msg, ["cybersecurity", "security", "protect my data", "cyber", "bch cyber"])) {
      return {
        text: "Our BCH_CYBER team can help protect your business and personal data with advanced cybersecurity solutions. Want a security assessment or advice? Just ask!",
        nextTopic: null
      };
    }
    // Contact info/location (updated phone number)
    if (fuzzyMatch(msg, ["contact", "phone number", "email address", "location", "where are you", "address"])) {
      return {
        text: "You can reach us at Moi Avenue, Tech Plaza, 3rd Floor. Call us at 0729-820-689 or email info@bonniecomputerhub.com. We’re always happy to help!",
        nextTopic: null
      };
    }
    // BCH as a school
    if (fuzzyMatch(msg, ["is bch a school", "is bonnie computer hub a school", "is bch a learning center", "is bch a web development school", "os bch a web development school"])) {
      return {
        text: "Bonnie Computer Hub is a leading tech learning center and digital solutions provider. We offer hands-on IT courses, web/software development, and more. Want to know about our programs or services?",
        nextTopic: null
      };
    }
    // Student count
    if (fuzzyMatch(msg, ["how many students", "number of students", "students handled", "how many students has the bch handled"])) {
      return {
        text: "We’ve helped over 2,000 students and professionals build digital skills! Want to join them? Ask about our next intake or available courses.",
        nextTopic: null
      };
    }
    // Instructor count
    if (fuzzyMatch(msg, ["how many instructors", "number of instructors", "how many instructors does the bch contain"])) {
      return {
        text: "BCH has a team of 10+ certified instructors, each with real-world experience in tech and education. Want to know more about our team?",
        nextTopic: null
      };
    }
    // Instructor/staff info
    if (fuzzyMatch(msg, ["instructors", "teachers", "trainers", "who teaches", "who are the instructors", "meet the team"])) {
      return {
        text: "Our instructors are certified experts with real-world experience. Want to know about a specific instructor or see their profiles? Just ask!",
        nextTopic: null
      };
    }
    // Certifications and skill badges
    if (fuzzyMatch(msg, ["certification", "certifications", "certified", "skill badge", "certificate", "do i get a certificate"])) {
      return {
        text: "Yes! Many of our courses include industry-recognized certificates and skill badges you can add to your CV or LinkedIn. Want details for a specific course?",
        nextTopic: null
      };
    }
    // Discounts, promotions, events
    if (fuzzyMatch(msg, ["discount", "promotion", "offer", "sale", "event", "upcoming event", "special offer", "current promotions", "upcoming events", "your offerings"])) {
      return {
        text: "We regularly offer discounts and special events! Ask about current promotions or upcoming events, and I’ll fill you in.",
        nextTopic: null
      };
    }
    // Tech support/troubleshooting
    if (fuzzyMatch(msg, ["technical support", "tech support", "troubleshoot", "problem with", "issue with", "fix my"])) {
      return {
        text: "Our support team can help with technical issues—whether it’s hardware, software, or online access. Please describe your problem, and I’ll get you the right help!",
        nextTopic: null
      };
    }
    // Partnerships/corporate training
    if (fuzzyMatch(msg, ["partnership", "corporate training", "business training", "company training", "partner with"])) {
      return {
        text: "We offer corporate training and partnership opportunities for businesses and organizations. Interested in a partnership or staff training? I’ll connect you with our team!",
        nextTopic: null
      };
    }
    // Testimonials/success stories
    if (fuzzyMatch(msg, ["testimonials", "success stories", "reviews", "what do people say", "student stories"])) {
      return {
        text: "Our students and clients love us! Many have landed great jobs or grown their businesses after working with BCH. Want to read some testimonials or success stories?",
        nextTopic: null
      };
    }
    // Book a visit or demo
    if (fuzzyMatch(msg, ["book a visit", "schedule a visit", "book demo", "schedule demo", "tour", "visit bch"])) {
      return {
        text: "We’d love to show you around! Let me know when you’d like to visit or if you want a live demo of our services, and I’ll help you book it.",
        nextTopic: null
      };
    }
    // Hours
    if (/hours|open|close|working time|when are you open/.test(msg)) {
      return { text: "Our support team is available Monday to Friday, 9am–6pm. You can also leave a message anytime!", nextTopic: null };
    }
    // Registration/Sign up/Create Account (strict match, always above course/class rules)
    if (fuzzyMatch(msg, ["register", "sign up", "signup", "create account", "open account", "enroll now"])) {
      return {
        text: "You can easily register for an account by clicking the 'Register' or 'Sign Up' button at the top of our website, or by visiting <a href='register.php' target='_blank'>the registration page</a>. If you need help with the process, just let me know!",
        nextTopic: null
      };
    }
    // Next intake
    if (fuzzyMatch(msg, ["next intake", "next class", "next start date", "upcoming intake", "upcoming class"])) {
      return {
        text: "Our next course intake is coming up soon! Please let me know which course you’re interested in, and I’ll share the exact start date and details. Would you like a list of available courses?",
        nextTopic: "courses"
      };
    }
    // Available courses
    if (fuzzyMatch(msg, ["available courses", "course list", "courses available", "what courses", "offerings"])) {
      return {
        text: "Here’s a quick overview of our available courses: Web Development, Cybersecurity, Networking, and more! Would you like to see the full course catalog or details about a specific course?",
        nextTopic: "courses"
      };
    }
    // Class schedules/times/days
    if (fuzzyMatch(msg, ["class schedules", "class schedule", "class times", "class time", "day of classes", "when are classes", "when are courses", "schedules"])) {
      return {
        text: "Our classes have flexible schedules, including evenings and weekends. Let me know which course you’re interested in, and I’ll share the schedule options!",
        nextTopic: "courses"
      };
    }
    // Enrollments
    if (fuzzyMatch(msg, ["enrollments", "enrollment", "enrolment", "enrolments"])) {
      return {
        text: "You can view and manage your enrollments in your student dashboard once logged in. If you’d like to enroll in a new course, just let me know which one!",
        nextTopic: "courses"
      };
    }
    // Frontend course details
    if (fuzzyMatch(msg, ["frontend development course", "front end course", "front-end course", "frontend course", "front end development", "front-end development"])) {
      return {
        text: `<b>Frontend Development Course</b><br>
        <b>Fee:</b> KES 5000<br>
        <b>Duration:</b> 8 weeks<br>
        <b>Modes:</b> Instructor-led & Self-paced<br>
        <b>Topics:</b> HTML, CSS, JavaScript, Responsive Design, UI/UX Basics, Version Control (Git), Portfolio Project<br>
        <b>Certification:</b> Yes, with skill badge<br>
        <b>Who is it for?</b> Beginners & aspiring web designers<br>
        <b>Ready to enroll?</b> <a href='register.php' target='_blank'>Register here</a> or ask for more details!`,
        nextTopic: null
      };
    }
    // Backend course details
    if (fuzzyMatch(msg, ["backend development course", "back end course", "back-end course", "backend course", "back end development", "back-end development"])) {
      return {
        text: `<b>Backend Development Course</b><br>
        <b>Fee:</b> KES 5000<br>
        <b>Duration:</b> 8 weeks<br>
        <b>Modes:</b> Instructor-led & Self-paced<br>
        <b>Topics:</b> PHP, MySQL, Node.js, API Development, Authentication, Server Management, Deployment<br>
        <b>Certification:</b> Yes, with skill badge<br>
        <b>Who is it for?</b> Those interested in server-side coding & databases<br>
        <b>Ready to enroll?</b> <a href='register.php' target='_blank'>Register here</a> or ask for more details!`,
        nextTopic: null
      };
    }
    // Full stack course details
    if (fuzzyMatch(msg, ["full stack development course", "fullstack course", "full-stack course", "full stack development", "full-stack development"])) {
      return {
        text: `<b>Full Stack Development Course</b><br>
        <b>Fee:</b> KES 10,000<br>
        <b>Duration:</b> 8 weeks<br>
        <b>Modes:</b> Instructor-led & Self-paced<br>
        <b>Topics:</b> Everything in Frontend + Backend, DevOps basics, Project Collaboration, Real-world Capstone Project<br>
        <b>Certification:</b> Yes, with skill badge<br>
        <b>Who is it for?</b> Anyone aiming for a complete web developer skillset<br>
        <b>Ready to enroll?</b> <a href='register.php' target='_blank'>Register here</a> or ask for more details!`,
        nextTopic: null
      };
    }
    // General course info
    if (fuzzyMatch(msg, ["courses", "course list", "available courses", "course options", "what courses", "what do you teach", "what can i learn", "learning options"])) {
      return {
        text: `<b>Our Most Popular Courses</b><br>
        • <b>Frontend Development</b> (KES 5000, 8 weeks)<br>
        • <b>Backend Development</b> (KES 5000, 8 weeks)<br>
        • <b>Full Stack Development</b> (KES 10,000, 8 weeks)<br>
        <br>
        All courses offer instructor-led and self-paced options, hands-on projects, and certification.<br>
        Want details on a specific course or ready to enroll? <a href='register.php' target='_blank'>Register here</a> or ask for more info!`,
        nextTopic: null
      };
    }
    // Course pricing
    if (fuzzyMatch(msg, ["what prices do you charge per course", "course prices", "how much per course", "what does a course cost", "course fee", "fees per course"])) {
      return {
        text: "Our course prices are: <br>• Frontend: KES 500<br>• Backend: KES 500<br>• Full Stack: KES 10,000<br>All are 8 weeks and include both instructor-led and self-paced options. For more, check our <a href='courses.php' target='_blank'>course catalog</a> or ask about a specific course!",
        nextTopic: null
      };
    }
    // Courses/enrollment/LMS (fuzzy match for typos and single-word typos)
    if (fuzzyMatch(msg, ["course", "courses", "class", "classes", "classses", "clas", "enroll", "learning", "lms", "training", "intake", "intakes"])) {
      return {
        text: "We offer a variety of IT courses through our Learning Management System (LMS). Would you like details on available courses, class schedules, or enrollment?",
        nextTopic: "courses"
      };
    }
    // Class start/schedule/time/day (fuzzy match)
    if (/when.*(class|course|intake).*start|start date|schedule|next intake/.test(msg) || fuzzyMatch(msg, ["time for classes", "day of classes", "class schedule", "class time", "when are classes", "when are courses"])) {
      return {
        text: "Our classes have multiple intakes and flexible schedules. Please let me know which course you’re interested in, and I’ll share the next available start date and class times!",
        nextTopic: "courses"
      };
    }
    // Payment/fees/discounts (fuzzy match for pay)
    if (/payment|fee(s)?|cost|price|how much|discount(s)?|offer(s)?/.test(msg) || fuzzyMatch(msg, ["pay", "how to pay", "make payment"])) {
      return {
        text: "Absolutely! We offer transparent pricing and sometimes have special discounts available. Would you like a payment link, fee breakdown, or info on current offers?",
        nextTopic: "payment"
      };
    }
    // Software/web development services
    if (/software|web development|webdev|website|app(s)?|application(s)?|develop(er|ment)|build (a|an)? (website|app)/.test(msg)) {
      return {
        text: "Yes, we offer professional software and web development services. Would you like to discuss your project or get a quote?",
        nextTopic: "software"
      };
    }
    // Offerings/services summary
    if (/offering(s)?|service(s)?|what do you offer|what do you provide|what can you do|what are your products/.test(msg)) {
      return {
        text: "Bonnie Computer Hub offers a full range of IT solutions: computer and laptop sales, software products, professional IT and web development services, and a Learning Management System (LMS) with a variety of tech courses. What are you interested in today?",
        nextTopic: null
      };
    }
    // Login
    if (fuzzyMatch(msg, ["login", "log in", "sign in", "how to login", "how to log in"])) {
      return {
        text: "To log in, click the 'Login' button at the top right of our website and enter your username and password. If you have trouble, let me know!",
        nextTopic: null
      };
    }
    // Reset password
    if (fuzzyMatch(msg, ["reset password", "forgot password", "change password", "recover password"])) {
      return {
        text: "No worries! You can reset your password by clicking 'Forgot Password' on the login page. If you need more help, I can connect you with support.",
        nextTopic: null
      };
    }
    // Instructor info
    if (fuzzyMatch(msg, ["instructor", "instructors", "teacher", "trainers"])) {
      return {
        text: "Our instructors are experienced IT professionals dedicated to helping you succeed. If you’d like to know more about a specific instructor or their qualifications, just let me know!",
        nextTopic: null
      };
    }
    // Certification
    if (/certificate|certification|accreditation/.test(msg)) {
      return {
        text: "Many of our courses include certification upon completion. Please let us know which course you're interested in for specifics.",
        nextTopic: null
      };
    }
    // Contact info
    if (/contact|phone|email|reach|address/.test(msg)) {
      return {
        text: "You can contact us at info@bonniecomputerhub.com or call us at +123-456-7890.",
        nextTopic: null
      };
    }
    // Laptops/products
    if (/laptop(s)?|hardware|product(s)?|sell|buy|stock|available/.test(msg)) {
      return {
        text: "We provide a wide range of laptops and computer products. Let us know what you're looking for, and we'll assist you!",
        nextTopic: null
      };
    }
    // Goodbye
    if (/bye|goodbye|see you|later|thanks|thank you/.test(msg)) {
      return {
        text: "Goodbye! If you have more questions, feel free to chat with us anytime.",
        nextTopic: null
      };
    }
    // Escalate to human agent if requested
    if (/human agent|real person|talk to (a|an)? (human|person|agent)|escalate|someone (else|real)/.test(msg)) {
      return {
        text: "Absolutely! I'll connect you with a human agent right away. Please hold on a moment while I get someone to assist you.",
        nextTopic: null
      };
    }
    // Default fallback: refer to human agent, but be even warmer and more human
    return {
      text: "I’m not sure I have the answer to that, but I’m always learning! 😊 If you want, you can try rephrasing your question, or I can connect you with a human agent for more help. And if you just want to chat, I’m here for that too!",
      nextTopic: null
    };
  }

  // Helper for time-based greeting
  function getTimeBasedGreeting() {
    const now = new Date();
    const hour = now.getHours();
    if (hour < 12) return "Good morning! How can I help you today?";
    if (hour < 18) return "Good afternoon! How can I help you today?";
    return "Good evening! How can I help you today?";
  }

  // Append message to chat
  function appendMessage(type, text, quickReplies) {
    // Only allow string or safe HTML for text
    if (typeof text !== "string") return;
    const messageWrapper = document.createElement("div");
    messageWrapper.className = `bch-chat-msg ${type} bch-chat-animate`;

    const messageSpan = document.createElement("span");
    if (type === "agent") {
      messageSpan.innerHTML = text;
      // Add quick reply buttons if provided
      if (quickReplies && Array.isArray(quickReplies)) {
        const qrDiv = document.createElement('div');
        qrDiv.className = 'bch-quick-replies';
        quickReplies.forEach(qr => {
          const btn = document.createElement('button');
          btn.className = 'bch-quick-reply-btn';
          btn.type = 'button';
          btn.innerText = qr;
          btn.onclick = function() {
            inputField.value = qr;
            sendMessage();
          };
          qrDiv.appendChild(btn);
        });
        messageWrapper.appendChild(qrDiv);
      }
    } else {
      messageSpan.textContent = text;
    }
    messageWrapper.appendChild(messageSpan);
    messagesBox.appendChild(messageWrapper);
    messagesBox.scrollTop = messagesBox.scrollHeight;
    setTimeout(() => messageWrapper.classList.remove('bch-chat-animate'), 600);
  }

  // Typing indicator
  function showTypingIndicator() {
    if (document.getElementById('bch-typing-indicator')) return;
    const typingDiv = document.createElement('div');
    typingDiv.id = 'bch-typing-indicator';
    typingDiv.className = 'bch-chat-msg agent';
    typingDiv.innerHTML = "<span><em>BCH is typing</em><span class='bch-typing-dots'>...</span></span>";
    messagesBox.appendChild(typingDiv);
    messagesBox.scrollTop = messagesBox.scrollHeight;
  }
  function removeTypingIndicator() {
    const typingDiv = document.getElementById('bch-typing-indicator');
    if (typingDiv) typingDiv.remove();
  }

  // Modified sendMessage to use typing indicator and quick replies
  function sendMessage(quickReplyText) {
    const message = quickReplyText || inputField.value.trim();
    if (!message) return;
    appendMessage("user", message);
    inputField.value = "";
    inputField.focus();
    showTypingIndicator();
    setTimeout(() => {
      removeTypingIndicator();
      const reply = getAutoReply(message, lastTopic, userTimeOfDay);
      // Add quick replies for certain topics
      let quickReplies = null;
      if (reply.nextTopic === "courses") {
        quickReplies = ["Frontend Development Course", "Backend Development Course", "Full Stack Development Course", "Fee Breakdown"];
      } else if (reply.nextTopic === "payment") {
        quickReplies = ["Fee Breakdown", "Discounts", "Register"];
      }
      appendMessage("agent", reply.text, quickReplies);
      lastTopic = reply.nextTopic;
      if (reply.userTimeOfDay) userTimeOfDay = reply.userTimeOfDay;
    }, 800);
  }

  // Send message on button click
  sendBtn.addEventListener("click", sendMessage);

  // Send message on Enter key
  inputField.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      sendMessage();
    }
  });

  // Close chat on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !chatBox.classList.contains("bch-chat-hidden")) {
      chatBox.classList.add("bch-chat-hidden");
      chatBox.setAttribute("aria-bch-chat-hidden", "true");
    }
  });
});
