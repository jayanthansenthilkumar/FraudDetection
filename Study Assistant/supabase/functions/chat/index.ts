import { serve } from "https://deno.land/std@0.168.0/http/server.ts";

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

// Tool functions for real-time information
const getCurrentTime = () => {
  const now = new Date();
  return {
    currentTime: now.toLocaleTimeString('en-US', { 
      timeZone: 'UTC',
      hour12: true,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    }),
    currentDate: now.toLocaleDateString('en-US', {
      timeZone: 'UTC',
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }),
    timestamp: now.toISOString(),
    timezone: 'UTC'
  };
};

const performCalculation = (expression: string) => {
  try {
    // Safe evaluation of mathematical expressions
    // Only allow numbers, basic operators, and parentheses
    const sanitized = expression.replace(/[^0-9+\-*/().\s]/g, '');
    const result = eval(sanitized);
    return { expression, result };
  } catch (error) {
    return { expression, error: "Invalid calculation" };
  }
};

// Execute tool calls
const executeTool = (toolName: string, args: any) => {
  console.log(`Executing tool: ${toolName}`, args);
  
  switch (toolName) {
    case 'get_current_time':
      return getCurrentTime();
    case 'perform_calculation':
      return performCalculation(args.expression);
    default:
      return { error: `Unknown tool: ${toolName}` };
  }
};

serve(async (req) => {
  if (req.method === 'OPTIONS') {
    return new Response(null, { headers: corsHeaders });
  }

  try {
    const { messages } = await req.json();
    const LOVABLE_API_KEY = Deno.env.get("LOVABLE_API_KEY");
    
    if (!LOVABLE_API_KEY) {
      throw new Error("LOVABLE_API_KEY is not configured");
    }

    console.log("Processing chat request with", messages.length, "messages");

    // System prompt for AI assistant with VLSI knowledge and tool access
    const systemPrompt = `You are a helpful and knowledgeable AI study assistant with access to real-time information. You excel at:

1. Answering general knowledge questions clearly and concisely
2. Providing detailed explanations for VLSI (Very Large Scale Integration) domain topics including:
   - Digital circuit design
   - CMOS technology
   - VLSI design methodologies
   - Semiconductor physics
   - IC fabrication processes
   - Design automation and CAD tools
   - Timing analysis and verification
   - Low power design techniques
3. Accessing real-time information like current time, date, and performing calculations

When users ask about the current time or date, use the get_current_time tool.
When users ask you to perform calculations, use the perform_calculation tool.

Keep your responses clear, educational, and encouraging. When explaining technical concepts, break them down into understandable parts.`;

    // Define available tools
    const tools = [
      {
        type: "function",
        function: {
          name: "get_current_time",
          description: "Get the current date and time in UTC. Use this when users ask about the current time, date, day, or any time-related information.",
          parameters: {
            type: "object",
            properties: {},
            required: []
          }
        }
      },
      {
        type: "function",
        function: {
          name: "perform_calculation",
          description: "Perform mathematical calculations. Use this when users ask you to calculate, compute, or solve mathematical expressions.",
          parameters: {
            type: "object",
            properties: {
              expression: {
                type: "string",
                description: "The mathematical expression to evaluate (e.g., '2 + 2', '15 * 3.14', '(10 + 5) / 3')"
              }
            },
            required: ["expression"]
          }
        }
      }
    ];

    const response = await fetch("https://ai.gateway.lovable.dev/v1/chat/completions", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${LOVABLE_API_KEY}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        model: "google/gemini-2.5-flash",
        messages: [
          { role: "system", content: systemPrompt },
          ...messages,
        ],
        tools: tools,
        tool_choice: "auto",
      }),
    });

    if (!response.ok) {
      if (response.status === 429) {
        console.error("Rate limit exceeded");
        return new Response(
          JSON.stringify({ error: "Rate limits exceeded, please try again later." }), 
          {
            status: 429,
            headers: { ...corsHeaders, "Content-Type": "application/json" },
          }
        );
      }
      if (response.status === 402) {
        console.error("Payment required");
        return new Response(
          JSON.stringify({ error: "Payment required, please add funds to your workspace." }), 
          {
            status: 402,
            headers: { ...corsHeaders, "Content-Type": "application/json" },
          }
        );
      }
      
      const errorText = await response.text();
      console.error("AI gateway error:", response.status, errorText);
      return new Response(
        JSON.stringify({ error: "AI service error" }), 
        {
          status: 500,
          headers: { ...corsHeaders, "Content-Type": "application/json" },
        }
      );
    }

    const data = await response.json();
    const choice = data.choices[0];
    
    // Check if AI wants to use a tool
    if (choice.message.tool_calls && choice.message.tool_calls.length > 0) {
      console.log("AI requested tool calls:", choice.message.tool_calls);
      
      // Execute all tool calls
      const toolResults = choice.message.tool_calls.map((toolCall: any) => {
        const args = JSON.parse(toolCall.function.arguments);
        const result = executeTool(toolCall.function.name, args);
        
        return {
          tool_call_id: toolCall.id,
          role: "tool",
          name: toolCall.function.name,
          content: JSON.stringify(result)
        };
      });

      // Send tool results back to AI for final response
      const followUpResponse = await fetch("https://ai.gateway.lovable.dev/v1/chat/completions", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${LOVABLE_API_KEY}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          model: "google/gemini-2.5-flash",
          messages: [
            { role: "system", content: systemPrompt },
            ...messages,
            choice.message,
            ...toolResults
          ],
        }),
      });

      if (!followUpResponse.ok) {
        throw new Error("Failed to get follow-up response after tool execution");
      }

      const followUpData = await followUpResponse.json();
      const finalResponse = followUpData.choices[0].message.content;
      
      console.log("AI response with tool results generated successfully");
      
      return new Response(
        JSON.stringify({ response: finalResponse }),
        { headers: { ...corsHeaders, "Content-Type": "application/json" } }
      );
    }
    
    // No tool calls, return direct response
    const aiResponse = choice.message.content;
    console.log("AI response generated successfully");

    return new Response(
      JSON.stringify({ response: aiResponse }),
      { headers: { ...corsHeaders, "Content-Type": "application/json" } }
    );
  } catch (error) {
    console.error("Chat error:", error);
    return new Response(
      JSON.stringify({ error: error instanceof Error ? error.message : "Unknown error" }),
      {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  }
});

